<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Helpers\ActivityLogger;
use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\DraftReject;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Services\BalanceService;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class DraftController extends Controller
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
                'user_account' => function ($account) {
                    return $account->with('bank');
                }])
                ->where('category', EnumTransactionCategory::WITHDRAWAL)
                ->where('status', EnumTransactionsStatus::PENDING);

            if (!empty($request->term)) {
                $transactions->whereHas('user', function ($user) use ($request) {
                    return $user->where('name', 'LIKE', "%{$request->term}%")->orWhere('username', 'LIKE', "%{$request->term}%");
                });
            }

            return response($transactions->orderBy('payment_at')->paginate(10), Response::HTTP_OK);
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
                'user_account' => function ($account) {
                    return $account->with('bank');
                }])
                ->where('category', EnumTransactionCategory::WITHDRAWAL)
                ->where('status', $request->status);

            if (!empty($request->term)) {
                $transactions->whereHas('user', function ($user) use ($request) {
                    return $user->where('name', 'LIKE', "%{$request->term}%")->orWhere('username', 'LIKE', "%{$request->term}%");
                });
            }

            return response($transactions->orderBy('payment_at')->paginate(10), Response::HTTP_OK);

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
            'draft' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::WITHDRAWAL])]
        ], [
            'draft.required' => 'O identificador da transação é obrigatório.',
            'draft.exists' => 'A transação não existe',
            'category.required' => 'Você deve informar a categoria da transação.',
            'category.in' => 'A categoria da transação não corresponde.'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::WITHDRAWAL)
                ->where('id', $request->draft)
                ->whereIn('status', [EnumTransactionsStatus::PENDING, EnumTransactionsStatus::PROCESSING])
                ->firstOrFail();

            $transaction->status = EnumTransactionsStatus::SUCCESS;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            ActivityLogger::log(trans('messages.withdrawal.done'), $transaction->user_id);

            DB::commit();
            return response([
                'message' => 'O saque foi efetuado com sucesso',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function process(Request $request)
    {
        $request->validate([
            'draft' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::WITHDRAWAL])]
        ], [
            'draft.required' => 'O identificador da transação é obrigatório.',
            'draft.exists' => 'A transação não existe',
            'category.required' => 'Você deve informar a categoria da transação.',
            'category.in' => 'A categoria da transação não corresponde.'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::WITHDRAWAL)
                ->where('id', $request->draft)
                ->where('status', EnumTransactionsStatus::PENDING)
                ->firstOrFail();

            $transaction->status = EnumTransactionsStatus::PROCESSING;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            ActivityLogger::log(trans('messages.withdrawal.processing'), $transaction->user_id);

            DB::commit();
            return response([
                'message' => 'O saque foi colocado em processamento com sucesso',
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
            'draft' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::WITHDRAWAL])],
            'reason' => 'required|min:3'
        ], [
            'draft.required' => 'O identificador da transação é obrigatório.',
            'draft.exists' => 'A transação não existe',
            'category.required' => 'Você deve informar a categoria da transação.',
            'category.in' => 'A categoria da transação não corresponde.',
            'reason.required' => 'Você deve informar o motivo da reprovação.',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::WITHDRAWAL)
                ->where('id', $request->draft)
                ->where('status', EnumTransactionsStatus::PENDING)
                ->firstOrFail();

            $transaction->status = EnumTransactionsStatus::REVERSED;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $user = User::findOrFail($transaction->user_id);

            Localization::setLocale($user);
            Mail::to($user->email)->send(new DraftReject($user, $request->reason));

            $this->balanceService::reverse($transaction);

            ActivityLogger::log(trans('messages.withdrawal.reversed', ['reason' => $request->reason]), $transaction->user_id);

            DB::commit();
            return response([
                'message' => 'O saque foi reprovado com sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
