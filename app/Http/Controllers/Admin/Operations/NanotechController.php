<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enum\EnumNanotechOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Helpers\ActivityLogger;
use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\DepositoReject;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Services\BalanceService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class NanotechController extends Controller
{
    protected $balanceService;

    public function __construct(BalanceService $balance)
    {
        $this->balanceService = $balance;
    }

    public function index()
    {
        try {
            $transactions = NanotechOperation::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'investment' => function ($investment) {
                    return $investment->with(['coin', 'type']);
                }
            ])
                ->whereIn('type',
                    [EnumNanotechOperationType::WITHDRAWAL, EnumNanotechOperationType::PROFIT_WITHDRAWAL]
                )
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return response($transactions, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function list($status = 1)
    {
        try {
            $transactions = NanotechOperation::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'investment' => function ($investment) {
                    return $investment->with(['coin', 'type']);
                }
            ])
                ->whereIn('type',
                    [EnumNanotechOperationType::WITHDRAWAL, EnumNanotechOperationType::PROFIT_WITHDRAWAL]
                )
                ->where('status', $status)
                ->orderBy('created_at', 'DESC')
                ->paginate(10);

            return response($transactions, Response::HTTP_OK);
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
            'category' => ['required', Rule::in([EnumTransactionCategory::DRAFT])]
        ], [
            'draft.required' => 'O identificador da transação é obrigatório.',
            'draft.exists' => 'A transação não existe',
            'category.required' => 'Você deve informar a categoria da transação.',
            'category.in' => 'A categoria da transação não corresponde.'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::DRAFT)
                ->where('id', $request->draft)
                ->whereIn('status', [EnumTransactionsStatus::PENDING, EnumTransactionsStatus::PROCESSING])
                ->firstOrFail();

            $transaction->status = EnumTransactionsStatus::SUCCESS;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            ActivityLogger::log(trans('message.withdrawal.done'), $transaction->user_id);

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

    public function reject(Request $request)
    {
        $request->validate([
            'draft' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::DRAFT])],
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

            $transaction = Transaction::where('category', EnumTransactionCategory::DRAFT)
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
            Mail::to($user->email)->send(new DepositoReject($user, $request->reason));

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
