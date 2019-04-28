<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{

    public function index()
    {
        try {

            $deposits = Transaction::where('category', EnumTransactionCategory::DEPOSIT);
            $deposits_pending = Transaction::where('category', EnumTransactionCategory::DEPOSIT)->where('status', EnumTransactionsStatus::PENDING);
            $deposits_paid = Transaction::where('category', EnumTransactionCategory::DEPOSIT)->where('status', EnumTransactionsStatus::SUCCESS);

            $drafts = Transaction::where('category', EnumTransactionCategory::DRAFT);
            $drafts_pending = Transaction::where('category', EnumTransactionCategory::DRAFT)->where('status', EnumTransactionsStatus::PENDING);
            $drafts_paid = Transaction::where('category', EnumTransactionCategory::DRAFT)->where('status', EnumTransactionsStatus::SUCCESS);

            return [
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

            ];
        } catch (\Exception $e) {
            return response([
                'message' => $e->getMessage(),
                'transactions' => null
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
