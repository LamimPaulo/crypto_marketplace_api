<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enum\EnumNanotechOperationStatus;
use App\Enum\EnumNanotechOperationType;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Helpers\ActivityLogger;
use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\NanotechWithdrawalReject;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
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
            'id' => 'required|exists:nanotech_operations,id',
            'type' => [
                'required',
                Rule::in([
                    EnumNanotechOperationType::WITHDRAWAL,
                    EnumNanotechOperationType::PROFIT_WITHDRAWAL,
                ])
            ],
        ], [
            'id.required' => 'O identificador da transação é obrigatório.',
            'id.exists' => 'A solicitação não existe',
            'type.required' => 'Você deve informar a categoria da transação.',
            'type.in' => 'A categoria da transação não corresponde.',
        ]);

        try {
            DB::beginTransaction();

            $operation = NanotechOperation::where('type', $request->type)
                ->where('id', $request->id)
                ->where('status', EnumNanotechOperationStatus::PENDING)
                ->firstOrFail();

            $operation->status = EnumTransactionsStatus::SUCCESS;
            $operation->save();

            $wallet = UserWallet::where([
                'coin_id' => $operation->investment->coin_id,
                'user_id' => $operation->user_id,
            ])->first();

            $transaction = Transaction::create([
                'user_id' => $operation->user_id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'toAddress' => $wallet->address,
                'amount' => abs($operation->amount),
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => $operation->investment->id == 3 ? EnumTransactionCategory::MASTERNODE : EnumTransactionCategory::NANOTECH,
                'fee' => 0,
                'tax' => 0,
                'tx' => '',
                'info' => 'Saque - ' . $operation->investment->type->type,
                'error' => '',
            ]);

            TransactionStatus::create([
                'transaction_id' => $transaction->id,
                'status' => $transaction->status
            ]);

            $this->balanceService::increments($transaction);

            ActivityLogger::log(trans('message.withdrawal.done'), $operation->user_id);

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
            'id' => 'required|exists:nanotech_operations,id',
            'type' => [
                'required',
                Rule::in([
                    EnumNanotechOperationType::WITHDRAWAL,
                    EnumNanotechOperationType::PROFIT_WITHDRAWAL,
                ])
            ],
            'reason' => 'required|min:3'
        ], [
            'id.required' => 'O identificador da transação é obrigatório.',
            'id.exists' => 'A solicitação não existe',
            'type.required' => 'Você deve informar a categoria da transação.',
            'type.in' => 'A categoria da transação não corresponde.',
            'reason.required' => 'Você deve informar o motivo da reprovação.',
        ]);

        try {
            DB::beginTransaction();

            $operation = NanotechOperation::where('type', $request->type)
                ->where('id', $request->id)
                ->where('status', EnumNanotechOperationStatus::PENDING)
                ->firstOrFail();

            $operation->status = EnumTransactionsStatus::REVERSED;
            $operation->save();

            if (EnumNanotechOperationType::WITHDRAWAL == $operation->type) {
                $operation->amount = abs($operation->amount);
                Nanotech::increments($operation);
            }

            if (EnumNanotechOperationType::PROFIT_WITHDRAWAL == $operation->type) {
                NanotechOperation::create([
                    'user_id' => $operation->user_id,
                    'amount' => abs($operation->amount),
                    'status' => EnumNanotechOperationStatus::SUCCESS,
                    'type' => EnumNanotechOperationType::PROFIT,
                    'created_at' => $operation->created_at,
                    'investment_id' => $operation->investment_id,
                    'profit_percent' => $operation->profit_percent,
                ]);
            }

            $user = User::findOrFail($operation->user_id);

            Localization::setLocale($user);
            Mail::to($user->email)->send(new NanotechWithdrawalReject($user, $request->reason));

            ActivityLogger::log(trans('messages.withdrawal.reversed', ['reason' => $request->reason]), $operation->user_id);

            DB::commit();
            return response([
                'message' => 'A retirada foi reprovada com sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
