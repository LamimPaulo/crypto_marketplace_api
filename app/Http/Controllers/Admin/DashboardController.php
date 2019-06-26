<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumStatusDocument;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Controller;
use App\Models\AdminDashboard;
use App\Models\Coin;
use App\Models\Nanotech\Nanotech;
use App\Models\Transaction;
use App\Models\User\Document;
use App\Models\User\UserWallet;
use App\Services\PermissionService;
use App\User;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{

    public function index()
    {
        try {

            $dashboard = AdminDashboard::first();
            return $dashboard;

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function general()
    {
        try {
            $levels = Transaction::where([
                'category' => EnumTransactionCategory::BUY_LEVEL,
                'status' => EnumTransactionsStatus::SUCCESS,
                'coin_id' => Coin::getByAbbr('BRL')->id
            ]);

            $levels_lqx = Transaction::where([
                'category' => EnumTransactionCategory::BUY_LEVEL,
                'status' => EnumTransactionsStatus::SUCCESS,
                'coin_id' => Coin::getByAbbr('LQX')->id
            ]);

            $data = [
                'users' => User::whereNotNull('email_verified_at')->count(),
                'incomplete_users' => User::whereNull('email_verified_at')->count(),
                'unverified_docs' => Document::where('status', EnumStatusDocument::PENDING)->where('document_type_id', 1)->count(),

                'levels' => $levels->count(),
                'levels_sold' => $levels->sum('amount'),

                'levels_lqx' => $levels_lqx->count(),
                'levels_lqx_sold' => $levels_lqx->sum('amount'),

                'balance_brl' => UserWallet::where('coin_id', 2)->sum('balance'),
            ];

            $dash = AdminDashboard::firstOrNew(['id' => 1]);
            $dash->update($data);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function withdrawals()
    {
        try {
            $withdrawals = Transaction::where('category', EnumTransactionCategory::WITHDRAWAL);
            $withdrawals_pending = Transaction::where([
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'status' => EnumTransactionsStatus::PENDING
            ]);
            $withdrawals_processing = Transaction::where([
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'status' => EnumTransactionsStatus::PROCESSING
            ]);
            $withdrawals_reversed = Transaction::where([
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'status' => EnumTransactionsStatus::REVERSED
            ]);
            $withdrawals_paid = Transaction::where([
                'category' => EnumTransactionCategory::WITHDRAWAL,
                'status' => EnumTransactionsStatus::SUCCESS
            ]);

            $data = [
                'withdrawals' => $withdrawals->count(),
                'withdrawals_amount' => $withdrawals->sum('amount'),
                'withdrawals_pending' => $withdrawals_pending->count(),
                'withdrawals_pending_amount' => $withdrawals_pending->sum('amount'),
                'withdrawals_paid' => $withdrawals_paid->count(),
                'withdrawals_paid_amount' => $withdrawals_paid->sum('amount'),
                'withdrawals_processing' => $withdrawals_processing->count(),
                'withdrawals_processing_amount' => $withdrawals_processing->sum('amount'),
                'withdrawals_reversed' => $withdrawals_reversed->count(),
                'withdrawals_reversed_amount' => $withdrawals_reversed->sum('amount'),
            ];

            $dash = AdminDashboard::firstOrNew(['id' => 1]);
            $dash->update($data);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deposits()
    {
        try {
            $deposits = Transaction::where('category', EnumTransactionCategory::DEPOSIT);
            $deposits_pending = Transaction::where([
                'category' => EnumTransactionCategory::DEPOSIT,
                'status' => EnumTransactionsStatus::PENDING
            ]);
            $deposits_rejected = Transaction::where('category', EnumTransactionCategory::DEPOSIT)
                ->whereIn('status', [
                    EnumTransactionsStatus::CANCELED, EnumTransactionsStatus::ERROR, EnumTransactionsStatus::REVERSED
                ]);
            $deposits_paid = Transaction::where([
                'category' => EnumTransactionCategory::DEPOSIT,
                'status' => EnumTransactionsStatus::SUCCESS
            ]);

            $data = [
                'deposits' => $deposits->count(),
                'deposits_amount' => $deposits->sum('amount'),
                'deposits_pending' => $deposits_pending->count(),
                'deposits_pending_amount' => $deposits_pending->sum('amount'),
                'deposits_rejected' => $deposits_rejected->count(),
                'deposits_rejected_amount' => $deposits_rejected->sum('amount'),
                'deposits_paid' => $deposits_paid->count(),
                'deposits_paid_amount' => $deposits_paid->sum('amount'),
            ];

            $dash = AdminDashboard::firstOrNew(['id' => 1]);
            $dash->update($data);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function nanotech()
    {
        try {
            $nanotech_lqx = Nanotech::where('type_id', 1)->sum('amount');
            $nanotech_btc = Nanotech::where('type_id', 2)->sum('amount');
            $masternode = Nanotech::where('type_id', 3)->sum('amount');

            $data = [
                'nanotech_lqx' => (string)sprintf("%.8f", $nanotech_lqx),
                'nanotech_btc' => (string)sprintf("%.8f", $nanotech_btc),
                'masternode' => (string)sprintf("%.8f", $masternode),
            ];

            $dash = AdminDashboard::firstOrNew(['id' => 1]);
            $dash->update($data);

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function crypto_operations()
    {
        try {
            $coins = Coin::where(['is_wallet' => true, 'is_crypto' => true])->get();
            $report = [];

            foreach ($coins as $coin) {
                $transactions_in = Transaction::where([
                    'category' => EnumTransactionCategory::TRANSACTION,
                    'type' => EnumTransactionType::IN,
                    'coin_id' => $coin->id
                ])->whereNull('sender_user_id');

                $transactions_out = Transaction::where([
                    'category' => EnumTransactionCategory::TRANSACTION,
                    'type' => EnumTransactionType::OUT,
                    'coin_id' => $coin->id
                ])->whereRaw("toAddress NOT IN (SELECT address FROM user_wallets WHERE coin_id = $coin->id)");

                $transactions_out_internal = Transaction::where([
                    'category' => EnumTransactionCategory::TRANSACTION,
                    'type' => EnumTransactionType::OUT,
                    'coin_id' => $coin->id
                ])->whereRaw("toAddress IN (SELECT address FROM user_wallets WHERE coin_id = $coin->id)");

                $buy_orders = Transaction::where([
                    'category' => EnumTransactionCategory::CONVERSION,
                    'type' => EnumTransactionType::IN,
                    'coin_id' => $coin->id
                ]);

                $sell_orders = Transaction::where([
                    'category' => EnumTransactionCategory::CONVERSION,
                    'type' => EnumTransactionType::OUT,
                    'coin_id' => $coin->id
                ]);

                $report[] = [
                    'coin' => $coin->abbr,
                    'balance' => UserWallet::where('coin_id', $coin->id)->sum('balance'),
                    'buy' => $buy_orders->count(),
                    'buy_amount' => $buy_orders->sum('amount') + $buy_orders->sum('tax') + $buy_orders->sum('fee'),
                    'sell' => $sell_orders->count(),
                    'sell_amount' => $sell_orders->sum('amount') + $sell_orders->sum('tax') + $sell_orders->sum('fee'),
                    'in' => $transactions_in->count(),
                    'in_amount' => $transactions_in->sum('amount') + $transactions_in->sum('tax') + $transactions_in->sum('fee'),
                    'out' => $transactions_out->count(),
                    'out_amount' => $transactions_out->sum('amount') + $transactions_out->sum('tax') + $transactions_out->sum('fee'),
                    'above_limit' => $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->count(),
                    'above_limit_amount' => $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('amount')
                        + $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('tax')
                        + $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('fee'),

                    'out_internal' => $transactions_out_internal->count(),
                    'out_amount_internal' => $transactions_out_internal->sum('amount') + $transactions_out_internal->sum('tax') + $transactions_out_internal->sum('fee'),
                    'above_limit_internal' => $transactions_out_internal->where('status', EnumTransactionsStatus::ABOVELIMIT)->count(),
                    'above_limit_amount_internal' => $transactions_out_internal->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('amount')
                        + $transactions_out_internal->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('tax')
                        + $transactions_out_internal->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('fee'),

                    'core_balance' => $coin->core_balance,
                    'core_status' => $coin->core_status,
                ];
            }

            $dash = AdminDashboard::firstOrNew(['id' => 1]);
            $dash->crypto_operations = json_encode($report);
            $dash->save();

        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
