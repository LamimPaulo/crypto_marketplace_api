<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumStatusDocument;
use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Transaction;
use App\Models\User\Document;
use App\User;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{

    public function index()
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

            $withdrawals = Transaction::where('category', EnumTransactionCategory::DRAFT);
            $withdrawals_pending = Transaction::where([
                'category' => EnumTransactionCategory::DRAFT,
                'status' => EnumTransactionsStatus::PENDING
            ]);
            $withdrawals_processing = Transaction::where([
                'category' => EnumTransactionCategory::DRAFT,
                'status' => EnumTransactionsStatus::PROCESSING
            ]);
            $withdrawals_reversed = Transaction::where([
                'category' => EnumTransactionCategory::DRAFT,
                'status' => EnumTransactionsStatus::REVERSED
            ]);
            $withdrawals_paid = Transaction::where([
                'category' => EnumTransactionCategory::DRAFT,
                'status' => EnumTransactionsStatus::SUCCESS
            ]);

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

            return [
                'users' => User::whereNotNull('email_verified_at')->count(),
                'incomplete_users' => User::whereNull('email_verified_at')->count(),
                'unverified_docs' => Document::where('status', EnumStatusDocument::PENDING)->count(),

                'levels' => $levels->count(),
                'levels_sold' => $levels->sum('amount'),

                'levels_lqx' => $levels_lqx->count(),
                'levels_lqx_sold' => $levels_lqx->sum('amount'),

                'deposits' => $deposits->count(),
                'deposits_amount' => $deposits->sum('amount'),
                'deposits_pending' => $deposits_pending->count(),
                'deposits_pending_amount' => $deposits_pending->sum('amount'),
                'deposits_rejected' => $deposits_rejected->count(),
                'deposits_rejected_amount' => $deposits_rejected->sum('amount'),
                'deposits_paid' => $deposits_paid->count(),
                'deposits_paid_amount' => $deposits_paid->sum('amount'),
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
                'crypto_operations' => $this->crypto_operations(),

            ];
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function crypto_operations()
    {
        $coins = Coin::where(['is_wallet' => true, 'is_crypto' => true])->get();
        $report = [];

        foreach($coins as $coin){
            $transactions_in = Transaction::where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::IN,
                'coin_id' => $coin->id
            ]);

            $transactions_out = Transaction::where([
                'category' => EnumTransactionCategory::TRANSACTION,
                'type' => EnumTransactionType::OUT,
                'coin_id' => $coin->id
            ]);

            $report[] = [
                'coin' => $coin->abbr,
                'in' => $transactions_in->count(),
                'in_amount' => $transactions_in->sum('amount') + $transactions_in->sum('tax') + $transactions_in->sum('fee'),
                'out' => $transactions_out->count(),
                'out_amount' => $transactions_out->sum('amount') + $transactions_out->sum('tax') + $transactions_out->sum('fee'),
                'above_limit' => $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->count(),
                'above_limit_amount' => $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('amount')
                                        + $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('tax')
                                        + $transactions_out->where('status', EnumTransactionsStatus::ABOVELIMIT)->sum('fee'),
                'core_balance' => $coin->core_balance,
                'core_status' => $coin->core_status,
            ];
        }

        return $report;
    }
}
