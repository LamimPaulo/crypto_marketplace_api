<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Helpers\ActivityLogger;
use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\TransactionReject;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Services\BalanceService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class CredminerController extends Controller
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
                'coin'])
                ->where('category', EnumTransactionCategory::CREDMINER)
                ->where('type', EnumTransactionType::IN)
                ->orderBy('created_at', 'ASC');

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

    public function byStatus(Request $request)
    {
        try {
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin'])
                ->where('category', EnumTransactionCategory::CREDMINER)
                ->where('type', EnumTransactionType::IN)
                ->where('status', $request->status)
                ->orderBy('created_at', 'ASC');

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

    //Crypto ou Fiat
    public function byCoin(Request $request)
    {
        try {
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin'])
                ->whereHas('coin', function ($coin) use ($request) {
                    return $coin->where('abbr', "{$request->coin}");
                })
                ->where('category', EnumTransactionCategory::CREDMINER)
                ->where('type', EnumTransactionType::IN)
                ->orderBy('created_at', 'ASC');

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

    public function cancel(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::CREDMINER])],
            'reason' => 'required|min:3'
        ], [
            'id.required' => 'O identificador da transação é obrigatório.',
            'id.exists' => 'A transação não existe',
            'reason.required' => 'Você deve informar o motivo do cancelamento.',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where([
                'id' => $request->id,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::CREDMINER,
                'status' => EnumTransactionsStatus::PENDING
            ])
                ->first();

            if (!$transaction) {
                throw new \Exception("A transação não pode ser cancelada.");
            }

            $transaction->status = EnumTransactionsStatus::CANCELED;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $user = User::findOrFail($transaction->user_id);

            Localization::setLocale($user);
            Mail::to($user->email)->send(new TransactionReject($user, $request->reason));

            ActivityLogger::log(trans('messages.transaction.canceled', ['reason' => $request->reason]), $transaction->user_id);

            DB::commit();
            return response([
                'message' => 'A transação foi cancelada com sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function accept(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::CREDMINER])],
        ], [
            'id.required' => 'O identificador da transação é obrigatório.',
            'id.exists' => 'A transação não existe',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where([
                'id' => $request->id,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::CREDMINER,
                'status' => EnumTransactionsStatus::PENDING
            ])
                ->first();

            if (!$transaction) {
                throw new \Exception("A transação não pode ser aprovada.");
            }

            User::findOrFail($transaction->user_id);

            BalanceService::increments($transaction);

            $transaction->status = EnumTransactionsStatus::SUCCESS;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id
            ]);

            ActivityLogger::log(trans('messages.transaction.success'), $transaction->user_id);

            DB::commit();
            return response([
                'message' => 'Transação Aprovada com Sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
