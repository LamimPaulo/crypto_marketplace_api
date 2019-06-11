<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Helpers\ActivityLogger;
use App\Helpers\Localization;
use App\Http\Controllers\Controller;
use App\Mail\TransactionReject;
use App\Models\Coin;
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

class TransactionsController extends Controller
{
    protected $balanceService;

    public function __construct(BalanceService $balance)
    {
        $this->balanceService = $balance;
    }

    public function index()
    {
        try {
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin'])
                ->where('category', EnumTransactionCategory::TRANSACTION)
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

    public function byStatus(Request $request)
    {
        try {
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin'])
                ->where('category', EnumTransactionCategory::TRANSACTION)
                ->where('status', $request->status)
                ->orderBy('created_at', 'DESC');

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

    public function byType(Request $request)
    {
        try {
            $transactions = Transaction::with([
                'user' => function ($user) {
                    return $user->with(['level', 'country']);
                },
                'coin'])
                ->where('category', EnumTransactionCategory::TRANSACTION)
                ->where('type', $request->type)
                ->whereNotIn('status', [EnumTransactionsStatus::ERROR, EnumTransactionsStatus::ABOVELIMIT, EnumTransactionsStatus::REVERSED])
                ->orderBy('created_at', 'DESC');

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

    public function reject(Request $request)
    {
        $request->validate([
            'crypto' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::TRANSACTION])],
            'reason' => 'required|min:3'
        ], [
            'crypto.required' => 'O identificador da transação é obrigatório.',
            'crypto.exists' => 'A transação não existe',
            'reason.required' => 'Você deve informar o motivo da reprovação.',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::TRANSACTION)
                ->where('id', $request->crypto)
                ->whereIn('status', [EnumTransactionsStatus::PENDING, EnumTransactionsStatus::ABOVELIMIT])
                ->firstOrFail();

            $transaction->status = EnumTransactionsStatus::REVERSED;
            $transaction->save();

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $user = User::findOrFail($transaction->user_id);

            Localization::setLocale($user);
            Mail::to($user->email)->send(new TransactionReject($user, $request->reason));

            $this->balanceService::reverse($transaction);

            ActivityLogger::log(trans('messages.transaction.reversed', ['reason' => $request->reason]), $transaction->user_id);

            DB::commit();
            return response([
                'message' => 'A transação foi estornada com sucesso.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function accept(Request $request)
    {
        $request->validate([
            'crypto' => 'required|exists:transactions,id',
            'category' => ['required', Rule::in([EnumTransactionCategory::TRANSACTION])],
        ], [
            'crypto.required' => 'O identificador da transação é obrigatório.',
            'crypto.exists' => 'A transação não existe',
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::where('category', EnumTransactionCategory::TRANSACTION)
                ->where('id', $request->crypto)
                ->whereIn('status', [EnumTransactionsStatus::PENDING, EnumTransactionsStatus::ABOVELIMIT, EnumTransactionsStatus::ERROR])
                ->firstOrFail();

            User::findOrFail($transaction->user_id);

            $TransactionsSend = new \App\Console\Commands\TransactionsSend();
            $TransactionsSend->connectionSendBTC($transaction->id);

            ActivityLogger::log(trans('messages.transaction.sent_blockchain'), $transaction->user_id);

            DB::commit();
            return response([
                'message' => 'Transação enviada para a blockchain.',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function balanceVerify($user_email)
    {
        try {
            $user = User::where(['email' => $user_email])->first();

            $transactions = Transaction::whereRaw(
                "(user_id = '{$user->id}' AND status IN (" . EnumTransactionsStatus::SUCCESS . ", " . EnumTransactionsStatus::REVERSED . ", " . EnumTransactionsStatus::ABOVELIMIT . ")) 
                OR (user_id = '{$user->id}' AND category = " . EnumTransactionCategory::WITHDRAWAL . " AND status IN (" . EnumTransactionsStatus::PENDING . ", " . EnumTransactionsStatus::PROCESSING . "))")
                ->orderBy('updated_at', 'ASC')->get();

            $coins = Coin::all();
            $balances = [];

            foreach ($coins as $c) {
                $balances[$c->abbr] = [
                    "balance" => 0,
                    "balance_computed" => UserWallet::where([
                        'user_id' => $user->id,
                        'coin_id' => $c->id,
                    ])->first()
                ];
            }

            foreach ($transactions as $transaction) {
                if ($transaction->type == EnumTransactionType::IN) {
                    $balances[$transaction->coin->abbr]['balance'] += floatval($transaction->total);
                } else {
                    $balances[$transaction->coin->abbr]['balance'] -= floatval($transaction->total);
                }
                $transaction['balances'] = $balances;
            }

            return response([
                'transactions' => $transactions,
                'balances' => $balances,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
