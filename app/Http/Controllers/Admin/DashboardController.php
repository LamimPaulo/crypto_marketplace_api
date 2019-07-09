<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumStatusDocument;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Nanotech\Nanotech;
use App\Models\Transaction;
use App\Models\User\Document;
use App\Models\User\UserWallet;
use App\User;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{

    public function coins()
    {
        return Coin::where([
            'is_wallet' => true,
            'is_crypto' => true,
        ])->get();
    }

    public function index()
    {
        try {
            $levels = Transaction::where([
                'category' => EnumTransactionCategory::BUY_LEVEL,
                'status' => EnumTransactionsStatus::SUCCESS,
                'coin_id' => Coin::getByAbbr('BRL')->id
            ]);

            $levels_btc = Transaction::where([
                'category' => EnumTransactionCategory::BUY_LEVEL,
                'status' => EnumTransactionsStatus::SUCCESS,
                'coin_id' => Coin::getByAbbr('BTC')->id
            ]);

            $levels_lqx = Transaction::where([
                'category' => EnumTransactionCategory::BUY_LEVEL,
                'status' => EnumTransactionsStatus::SUCCESS,
                'coin_id' => Coin::getByAbbr('LQX')->id
            ]);

            $nanotech_lqx = Nanotech::where('type_id', 1)->sum('amount');
            $nanotech_btc = Nanotech::where('type_id', 2)->sum('amount');
            $masternode = Nanotech::where('type_id', 3)->sum('amount');

            return [
                'users' => User::whereNotNull('email_verified_at')->count(),
                'incomplete_users' => User::whereNull('email_verified_at')->count(),
                'canceled_users' => User::where('is_canceled', true)->count(),
                'unverified_docs' => Document::where('status', EnumStatusDocument::PENDING)->where('document_type_id', 1)->count(),

                'levels' => $levels->count(),
                'levels_sold' => $levels->sum('amount'),

                'levels_lqx' => $levels_lqx->count(),
                'levels_lqx_sold' => $levels_lqx->sum('amount'),

                'levels_btc' => $levels_btc->count(),
                'levels_btc_sold' => $levels_btc->sum('amount'),

                'nanotech_lqx' => (string)sprintf("%.8f", $nanotech_lqx),
                'nanotech_btc' => (string)sprintf("%.8f", $nanotech_btc),
                'masternode' => (string)sprintf("%.8f", $masternode),
            ];

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function fiat($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr)->id;

            $withdrawals = Transaction::where([
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'coin_id' => $coin
            ]);

            $withdrawals_pending = Transaction::where([
                'coin_id' => $coin,
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'status' => EnumTransactionsStatus::PENDING
            ]);
            $withdrawals_processing = Transaction::where([
                'coin_id' => $coin,
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'status' => EnumTransactionsStatus::PROCESSING
            ]);
            $withdrawals_reversed = Transaction::where([
                'coin_id' => $coin,
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'status' => EnumTransactionsStatus::REVERSED
            ]);
            $withdrawals_paid = Transaction::where([
                'coin_id' => $coin,
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'status' => EnumTransactionsStatus::SUCCESS
            ]);

            $deposits = Transaction::where([
                'category' => EnumTransactionCategory::DEPOSIT,
                'coin_id' => $coin
            ]);

            $deposits_pending = Transaction::where([
                'category' => EnumTransactionCategory::DEPOSIT,
                'status' => EnumTransactionsStatus::PENDING,
                'coin_id' => $coin
            ]);
            $deposits_rejected = Transaction::where([
                'category' => EnumTransactionCategory::DEPOSIT,
                'coin_id' => $coin
            ])
                ->whereIn('status', [
                    EnumTransactionsStatus::CANCELED, EnumTransactionsStatus::ERROR, EnumTransactionsStatus::REVERSED
                ]);
            $deposits_paid = Transaction::where([
                'category' => EnumTransactionCategory::DEPOSIT,
                'status' => EnumTransactionsStatus::SUCCESS,
                'coin_id' => $coin
            ]);

            return [
                'deposits' => $deposits->count(),
                'deposits_amount' => sprintf("%.2f", $deposits->sum('amount')),
                'deposits_pending' => $deposits_pending->count(),
                'deposits_pending_amount' => sprintf("%.2f", $deposits_pending->sum('amount')),
                'deposits_rejected' => $deposits_rejected->count(),
                'deposits_rejected_amount' => sprintf("%.2f", $deposits_rejected->sum('amount')),
                'deposits_paid' => $deposits_paid->count(),
                'deposits_paid_amount' => sprintf("%.2f", $deposits_paid->sum('amount')),
                'withdrawals' => $withdrawals->count(),
                'withdrawals_amount' => sprintf("%.2f", $withdrawals->sum('amount')),
                'withdrawals_pending' => $withdrawals_pending->count(),
                'withdrawals_pending_amount' => sprintf("%.2f", $withdrawals_pending->sum('amount')),
                'withdrawals_paid' => $withdrawals_paid->count(),
                'withdrawals_paid_amount' => sprintf("%.2f", $withdrawals_paid->sum('amount')),
                'withdrawals_processing' => $withdrawals_processing->count(),
                'withdrawals_processing_amount' => sprintf("%.2f", $withdrawals_processing->sum('amount')),
                'withdrawals_reversed' => $withdrawals_reversed->count(),
                'withdrawals_reversed_amount' => sprintf("%.2f", $withdrawals_reversed->sum('amount')),
                'balance' => sprintf("%.2f", UserWallet::where('coin_id', $coin)->sum('balance')),
            ];

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_balance($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);

            return [
                'balance' => sprintf("%.8f", UserWallet::where('coin_id', $coin->id)->sum('balance')),
                'core_balance' => $coin->core_balance,
                'core_status' => $coin->core_status,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_above_limit($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);
            $transactions_out = Transaction::where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::OUT,
                'status' => EnumTransactionsStatus::ABOVELIMIT,
                'coin_id' => $coin->id
            ])->whereRaw("toAddress NOT IN (SELECT address FROM user_wallets WHERE coin_id = $coin->id)");

            $above_limit = $transactions_out->count();
            $above_limit_amount = sprintf("%.8f", $transactions_out->sum('amount')
                + $transactions_out->sum('tax')
                + $transactions_out->sum('fee'));

            return [
                'above_limit' => $above_limit,
                'above_limit_amount' => $above_limit_amount,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_above_internal($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);
            $transactions_out = Transaction::where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::OUT,
                'status' => EnumTransactionsStatus::ABOVELIMIT,
                'coin_id' => $coin->id
            ])->whereRaw("toAddress IN (SELECT address FROM user_wallets WHERE coin_id = $coin->id)");

            $above_limit = $transactions_out->count();
            $above_limit_amount = sprintf("%.8f", $transactions_out->sum('amount')
                + $transactions_out->sum('tax')
                + $transactions_out->sum('fee'));

            return [
                'above_internal' => $above_limit,
                'above_internal_amount' => $above_limit_amount,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_in($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);
            $transactions_in = Transaction::where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::IN,
                'coin_id' => $coin->id
            ])->whereNull('sender_user_id');

            $in = $transactions_in->count();
            $in_amount = sprintf("%.8f", $transactions_in->sum('amount')
                + $transactions_in->sum('tax')
                + $transactions_in->sum('fee'));

            return [
                'in' => $in,
                'in_amount' => $in_amount,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_buy($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);

            $buy_orders = Transaction::where([
                'category' => EnumTransactionCategory::CONVERSION,
                'type' => EnumTransactionType::IN,
                'coin_id' => $coin->id
            ]);

            $buy = $buy_orders->count();
            $buy_amount = sprintf("%.8f", $buy_orders->sum('amount'));

            return [
                'buy' => $buy,
                'buy_amount' => $buy_amount,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_sell($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);

            $sell_orders = Transaction::where([
                'category' => EnumTransactionCategory::CONVERSION,
                'type' => EnumTransactionType::OUT,
                'coin_id' => $coin->id
            ]);

            $sell = $sell_orders->count();
            $sell_amount = sprintf("%.8f", $sell_orders->sum('amount'));

            return [
                'sell' => $sell,
                'sell_amount' => $sell_amount,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_out($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);
            $transactions_out = Transaction::where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::OUT,
                'coin_id' => $coin->id
            ])->whereRaw("toAddress NOT IN (SELECT address FROM user_wallets WHERE coin_id = $coin->id)");

            $out = $transactions_out->count();
            $out_amount = sprintf("%.8f", $transactions_out->sum('amount')
                + $transactions_out->sum('tax')
                + $transactions_out->sum('fee'));

            return [
                'out' => $out,
                'out_amount' => $out_amount,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_out_internal($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);
            $transactions_out = Transaction::where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::OUT,
                'coin_id' => $coin->id
            ])->whereRaw("toAddress IN (SELECT address FROM user_wallets WHERE coin_id = $coin->id)");

            $out = $transactions_out->count();
            $out_amount = sprintf("%.8f", $transactions_out->sum('amount')
                + $transactions_out->sum('tax')
                + $transactions_out->sum('fee'));

            return [
                'out_internal' => $out,
                'out_internal_amount' => $out_amount,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto($abbr)
    {
        try {
            $coin = Coin::getByAbbr($abbr);

            $query = Transaction::where('coin_id', $coin->id);
            $query_transactions_in = clone $query;
            $query_transactions_out = clone $query;
            $query_transactions_out_internal = clone $query;
            $query_buy_orders = clone $query;
            $query_sell_orders = clone $query;

            //TRANSACTIONS IN
            $transactions_in = $query_transactions_in->where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::IN,

            ])->whereNull('sender_user_id');

            return $transactions_in;

            $in = $transactions_in->count();
            $in_amount = $transactions_in->sum('amount') + $transactions_in->sum('tax') + $transactions_in->sum('fee');

            //TRANSACTIONS OUT
            $transactions_out = $query_transactions_out->where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::OUT,

            ])->whereRaw("toAddress NOT IN (SELECT address FROM user_wallets WHERE coin_id = $coin->id)");

            $out = $transactions_out->count();
            $out_amount = $transactions_out->sum('amount') + $transactions_out->sum('tax') + $transactions_out->sum('fee');

            //TRANSACTIONS OUT INTERNAL
            $transactions_out_internal = $query_transactions_out_internal->where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::OUT
            ])->whereRaw("toAddress IN (SELECT address FROM user_wallets WHERE coin_id = $coin->id)");

            $out_internal = $transactions_out_internal->count();
            $out_amount_internal = $transactions_out_internal->sum('amount') + $transactions_out_internal->sum('tax') + $transactions_out_internal->sum('fee');


            //ORDERs
            $buy_orders = $query_buy_orders->where([
                'category' => EnumTransactionCategory::CONVERSION,
                'type' => EnumTransactionType::IN
            ]);

            $sell_orders = $query_sell_orders->where([
                'category' => EnumTransactionCategory::CONVERSION,
                'type' => EnumTransactionType::OUT
            ]);

            $buy = $buy_orders->count();
            $buy_amount = $buy_orders->sum('amount') + $buy_orders->sum('tax') + $buy_orders->sum('fee');
            $sell = $sell_orders->count();
            $sell_amount = $sell_orders->sum('amount') + $sell_orders->sum('tax') + $sell_orders->sum('fee');

            return [
                'coin' => $coin->abbr,
                'balance' => UserWallet::where('coin_id', $coin->id)->sum('balance'),
                'in' => $in,
                'in_amount' => $in_amount,
                'out' => $out,
                'out_amount' => $out_amount,
                'out_internal' => $out_internal,
                'out_amount_internal' => $out_amount_internal,

                'buy' => $buy,
                'buy_amount' => $buy_amount,
                'sell' => $sell,
                'sell_amount' => $sell_amount,

//                'above_limit' => $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->count(),
//                'above_limit_amount' => $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('amount')
//                    + $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('tax')
//                    + $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('fee'),
//
//
//                'above_limit_internal' => $transactions_out_internal->where('status', EnumTransactionsStatus::ABOVELIMIT)->count(),
//                'above_limit_amount_internal' => $transactions_out_internal->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('amount')
//                    + $transactions_out_internal->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('tax')
//                    + $transactions_out_internal->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('fee'),

                'core_balance' => $coin->core_balance,
                'core_status' => $coin->core_status,
            ];

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
