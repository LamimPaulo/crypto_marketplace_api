<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\DepositoReject;
use App\Models\System\ActivityLogger;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Services\BalanceService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class DepositController extends Controller
{
    protected $balanceService;

    public function __construct(BalanceService $balance)
    {
        $this->balanceService = $balance;
    }

    public function index(Request $request)
    {
        try {
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin',
                'system_account' => function ($account) {
                    return $account->with('bank');
                }])
                ->where('category', EnumTransactionCategory::DEPOSIT)
                ->where('status', EnumTransactionsStatus::PENDING)
                ->orderBy('created_at');

            if (!empty($request->term)) {
                $transactions->whereHas('user', function ($user) use ($request) {
                    return $user->where('name', 'LIKE', "%{$request->term}%")->orWhere('username', 'LIKE', "%{$request->term}%");
                });
            }

            return response($transactions->paginate(10), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function list(Request $request)
    {
        try {
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin',
                'system_account' => function ($account) {
                    return $account->with('bank');
                }])
                ->where('category', EnumTransactionCategory::DEPOSIT)
                ->where('status', '<>', EnumTransactionsStatus::PENDING)
                ->orderBy('created_at');

            if (!empty($request->term)) {
                $transactions->whereHas('user', function ($user) use ($request) {
                    return $user->where('name', 'LIKE', "%{$request->term}%")->orWhere('username', 'LIKE', "%{$request->term}%");
                });
            }

            return response($transactions->paginate(10), Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function accept(Request $request)
    {
        $request->validate([
            'deposit' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::DEPOSIT])]
        ], [
            'deposit.required' => 'O identificador da transação é obrigatório.',
            'deposit.exists' => 'A transação não existe',
            'category.required' => 'Você deve informar a categoria da transação.',
            'category.in' => 'A categoria da transação não corresponde.'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::DEPOSIT)
                ->where('id', $request->deposit)
                ->where('status', EnumTransactionsStatus::PENDING)
                ->firstOrFail();

            $transaction->status = EnumTransactionsStatus::SUCCESS;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $this->balanceService::increments($transaction);

            $this->activity($transaction, trans('messages.deposit.done'));

            DB::commit();
            return response([
                'message' => 'O depósito foi confirmado com sucesso',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function reject(Request $request)
    {
        $request->validate([
            'deposit' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::DEPOSIT])],
            'reason' => 'required|min:3'
        ], [
            'deposit.required' => 'O identificador da transação é obrigatório.',
            'deposit.exists' => 'A transação não existe',
            'category.required' => 'Você deve informar a categoria da transação.',
            'category.in' => 'A categoria da transação não corresponde.',
            'reason.required' => 'Você deve informar o motivo da reprovação.',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::DEPOSIT)
                ->where('id', $request->deposit)
                ->where('status', EnumTransactionsStatus::PENDING)
                ->firstOrFail();

            $transaction->status = EnumTransactionsStatus::CANCELED;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $user = User::findOrFail($transaction->user_id);

            Localization::setLocale($user);
            Mail::to($user->email)->send(new DepositoReject($user, $request->reason));

            $this->activity($transaction, trans('messages.deposit.rejected', ['reason' => $request->reason]));

            DB::commit();
            return response([
                'message' => 'O depósito foi reprovado com sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function activity($transaction, $message)
    {
        ActivityLogger::create([
            'log_name' => 'hist',
            'description' => $message,
            'subject_id' => $transaction->user_id,
            'subject_type' => User::class,
            'properties' => json_encode(['amount' => $transaction->amount])
        ]);
    }
}
