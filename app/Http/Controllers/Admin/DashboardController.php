<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumUserWalletType;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{

    public function index()
    {
        try {
            $transactions = Transaction::all();

            $deposits = $transactions->where('category', EnumTransactionCategory::DEPOSIT);
            $deposits_pending = $deposits->where('status', EnumTransactionsStatus::PENDING);
            $deposits_paid = $deposits->where('status', EnumTransactionsStatus::SUCCESS);
            $drafts = $transactions->where('category', EnumTransactionCategory::DRAFT);
            $drafts_pending = $drafts->where('status', EnumTransactionsStatus::PENDING);
            $drafts_paid = $drafts->where('status', EnumTransactionsStatus::SUCCESS);

            return response([
                'total' => $transactions->count(),
                'deposits' => $deposits->count(),
                'deposits_amount' => $deposits->sum('amount'),
                'deposits_pending' => $deposits_pending->count(),
                'deposits_pending_amount' => $deposits_pending->sum('amount'),
                'deposits_paid' => $deposits_paid->count(),
                'deposits_paid_amount' => $deposits_paid->sum('amount'),
                'drafts' => $drafts->count(),
                'drafts_amount' => $drafts->sum('amount'),
                'drafts_pending' => $drafts_pending->count(),
                'drafts_pending_amount' => $drafts_pending->sum('amount'),
                'drafts_paid' => $drafts_paid->count(),
                'drafts_paid_amount' => $drafts_paid->sum('amount'),

            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
