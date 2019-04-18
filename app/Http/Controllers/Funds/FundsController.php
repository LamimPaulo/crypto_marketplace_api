<?php

namespace App\Http\Controllers\Funds;

use App\Enum\EnumFundTransactionCategory;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FundRequest;
use App\Models\CoinQuote;
use App\Models\Funds\FundBalances;
use App\Models\Funds\FundBalancesHists;
use App\Models\Funds\FundCoins;
use App\Models\Funds\Funds;
use App\Models\Funds\FundTransaction;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User\UserWallet;
use App\Services\BalanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

class FundsController extends Controller
{
    public function index()
    {
        try {
            $funds = Funds::with([
                'coins' => function ($coins) {
                    return $coins->with('coin')->orderBy('percent', 'DESC');
                },
                'coin' => function ($coin) {
                    return $coin->with([
                        'wallets' => function ($wallets) {
                            return $wallets->where('user_id', auth()->user()->id);
                        }
                    ]);
                },
            ])->orderBy('is_active', 'DESC');

            return response([
                'status' => 'success',
                'count' => $funds->count(),
                'funds' => $funds->get()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function pieChart($fund)
    {
        try {
            $funds = FundCoins::with(['coin'])->where('fund_id', $fund)->orderBy('percent', 'DESC')->get();
            $coins = [];
            foreach ($funds as $fund) {
                array_push($coins, (float)$fund->percent);
            }
            return $coins;

        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function show($fund_id)
    {
        try {
            Funds::where('is_active', true)->findOrFail($fund_id);

            $balance = FundBalances::where(['fund_id' => $fund_id, 'user_id' => auth()->user()->id])->first();

            if (!$balance) {
                throw new \Exception(trans('messages.products.fund_not_acquired'));
            }

            return response([
                'status' => 'success',
                'count' => 1,
                'balance' => $balance
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'count' => 0,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function buy(FundRequest $request)
    {
        try {
            $values = $this->estimateBuyTax($request);
            $acquired = $values['total'] - $values['tax'];
            $fund = Funds::where('is_active', true)->findOrFail($request->fund_id);

            if (!$values['balance_valid']) {
                throw new \Exception(trans('messages.wallet.insuficient_balance'));
            }

            $wallet = UserWallet::where(['user_id' => auth()->user()->id, 'type' => EnumUserWalletType::WALLET, 'coin_id' => $fund->coin_id])->first();

            DB::beginTransaction();

            $fundBalance = FundBalances::where([
                'user_id' => auth()->user()->id,
                'fund_id' => $request->fund_id
            ])->first();

            if (!$fundBalance) {
                $fundBalance = FundBalances::create([
                    'user_id' => auth()->user()->id,
                    'fund_id' => $request->fund_id,
                    'balance_free' => 0,
                    'balance_blocked' => 0,
                    'end_date' => Carbon::now()->addMonths($fund->validity)
                ]);
            }

            FundBalances::increments_blocked($fundBalance, $acquired);

            FundBalancesHists::create([
                'fund_balance_id' => $fundBalance->id,
                'balance_free' => $fundBalance->balance_free,
                'balance_blocked' => $fundBalance->balance_blocked
            ]);

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'amount' => $acquired,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::OUT,
                'category' => EnumTransactionCategory::FUND,
                'confirmation' => 0,
                'fee' => 0,
                'tax' => $values['tax'],
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Aquisição de ' . $fund->name,
            ]);

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            FundTransaction::create([
                'user_id' => $transaction->user_id,
                'fund_id' => $fund->id,
                'coin_id' => $transaction->coin_id,
                'transaction_id' => $transaction->id,
                'value' => $transaction->amount,
                'tax' => $transaction->tax,
                'profit_percent' => 0,
                'type' => EnumTransactionType::IN,
                'category' => EnumFundTransactionCategory::PURCHASE,
                'status' => $transaction->status,
            ]);

            $balanceService = new BalanceService();
            $balanceService::decrements($transaction);

            DB::commit();
            return response([
                'status' => 'success',
                'message' => trans('messages.products.hiring_success'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function earlyRedemption(Request $request)
    {
        try {
            $fundBalance = FundBalances::with('fund')
                ->where([
                    'user_id' => auth()->user()->id,
                    'id' => $request->id
                ])->firstOrFail();

            DB::beginTransaction();

            if ($fundBalance->balance_blocked === 0) {
                throw new \Exception("Sem saldo disponível para realizar a operação.");
            }

            $wallet = UserWallet::where([
                'user_id' => auth()->user()->id,
                'type' => EnumUserWalletType::WALLET,
                'coin_id' => $fundBalance->fund->coin_id])->firstOrFail();

            $tax = $fundBalance->balance_blocked * $fundBalance->fund->early_redemption_tax / 100;

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'amount' => $fundBalance->balance_blocked - $tax,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::FUND,
                'confirmation' => 0,
                'fee' => 0,
                'tax' => $tax,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Antecipação de Fundo (' . $fundBalance->fund->name .')',
            ]);

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $balanceService = new BalanceService();
            $balanceService::increments($transaction);

            FundTransaction::create([
                'user_id' => auth()->user()->id,
                'fund_id' => $fundBalance->fund_id,
                'transaction_id' => $transaction->id,
                'coin_id' => $fundBalance->fund->coin_id,
                'value' => $fundBalance->balance_blocked - $tax,
                'tax' => $tax,
                'profit_percent' => 0,
                'type' => EnumTransactionType::OUT,
                'category' => EnumFundTransactionCategory::EARLY_WITHDRAWAL,
                'status' => EnumTransactionsStatus::SUCCESS,
            ]);

            FundBalances::decrements_blocked($fundBalance, $fundBalance->balance_blocked);

            $fundBalance = FundBalances::with('fund')
                ->where([
                    'user_id' => auth()->user()->id,
                    'id' => $request->id
                ])->firstOrFail();

            FundBalancesHists::create([
                'fund_balance_id' => $fundBalance->id,
                'balance_free' => $fundBalance->balance_free,
                'balance_blocked' => $fundBalance->balance_blocked
            ]);

            DB::commit();
            return response([
                'status' => 'success',
                'message' => trans('messages.withdrawal.success'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function withdrawal(Request $request)
    {
        try {
            $fundBalance = FundBalances::with('fund')
                ->where([
                    'user_id' => auth()->user()->id,
                    'id' => $request->id
                ])->firstOrFail();

            DB::beginTransaction();

            if ($fundBalance->balance_free === 0) {
                throw new \Exception("Sem saldo disponível para realizar a operação.");
            }

            $wallet = UserWallet::where([
                'user_id' => auth()->user()->id,
                'type' => EnumUserWalletType::WALLET,
                'coin_id' => $fundBalance->fund->coin_id])->firstOrFail();

            $tax = $fundBalance->balance_free * $fundBalance->fund->redemption_tax / 100;

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'coin_id' => $wallet->coin_id,
                'wallet_id' => $wallet->id,
                'amount' => $fundBalance->balance_free - $tax,
                'status' => EnumTransactionsStatus::SUCCESS,
                'type' => EnumTransactionType::IN,
                'category' => EnumTransactionCategory::FUND,
                'confirmation' => 0,
                'fee' => 0,
                'tax' => $tax,
                'tx' => Uuid::uuid4()->toString(),
                'info' => 'Lucro de Fundo (' . $fundBalance->fund->name .')',
            ]);

            TransactionStatus::create([
                'status' => $transaction->status,
                'transaction_id' => $transaction->id,
            ]);

            $balanceService = new BalanceService();
            $balanceService::increments($transaction);

            FundTransaction::create([
                'user_id' => auth()->user()->id,
                'fund_id' => $fundBalance->fund_id,
                'transaction_id' => $transaction->id,
                'coin_id' => $fundBalance->fund->coin_id,
                'value' => $fundBalance->balance_free - $tax,
                'tax' => $tax,
                'profit_percent' => 0,
                'type' => EnumTransactionType::OUT,
                'category' => EnumFundTransactionCategory::PROFIT_WITHDRAWAL,
                'status' => EnumTransactionsStatus::SUCCESS,
            ]);

            FundBalances::decrements_free($fundBalance, $fundBalance->balance_free);

            $fundBalance = FundBalances::with('fund')
                ->where([
                    'user_id' => auth()->user()->id,
                    'id' => $request->id
                ])->firstOrFail();

            FundBalancesHists::create([
                'fund_balance_id' => $fundBalance->id,
                'balance_blocked' => $fundBalance->balance_blocked,
                'balance_free' => $fundBalance->balance_free
            ]);

            DB::commit();
            return response([
                'status' => 'success',
                'message' => trans('messages.withdrawal.success'),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {

            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function estimateBuyTax(FundRequest $request)
    {
        $fund = Funds::with('coin')->where('is_active', true)->findOrFail($request->fund_id);

        $balanceService = new BalanceService();

        $price = $fund->price;

        $quotes = $request->quotes * $price;
        $tax = $quotes * ($fund->buy_tax / 100);
        $total = $quotes + $tax;
        $isvalid = true;

        if (!$balanceService->verifyBalance($total, $fund->coin->abbr)) {
            $isvalid = false;
        }

        return [
            'price' => $price * 1,
            'tax' => $tax,
            'total' => $total,
            'balance_valid' => $isvalid
        ];
    }

    public function userList()
    {
        $funds = FundBalances::with([
            'fund' => function ($fund) {
                return $fund->with([
                    'coin' => function ($coin) {
                        return $coin->with([
                            'wallets' => function ($wallets) {
                                return $wallets->where('user_id', auth()->user()->id);
                            }
                        ]);
                    },
                    'coins' => function ($coins) {
                        return $coins->with('coin')->orderBy('percent', 'DESC');
                    },
                ]);
            },
            'user'
        ])->where('user_id', auth()->user()->id)->orderBy('updated_at', 'DESC')->get();

        return response([
            'status' => 'success',
            'funds' => $funds,
        ], Response::HTTP_OK);

    }
}
