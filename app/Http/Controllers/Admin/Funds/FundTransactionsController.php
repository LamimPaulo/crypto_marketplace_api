<?php

namespace App\Http\Controllers\Admin\Funds;

use App\Enum\EnumFundTransactionCategory;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Controller;
use App\Models\Funds\FundTransaction;
use Symfony\Component\HttpFoundation\Response;

class FundTransactionsController extends Controller
{
    public function index()
    {

    }

    public function transactions($fund_id)
    {
        $transactions = FundTransaction::with([
            'user', 'coin'
        ])->where('fund_id', $fund_id)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return response($transactions, Response::HTTP_OK);
    }

    public function profits($fund_id)
    {
        $transactions = FundTransaction::with([
            'user', 'coin'
        ])->where([
            'fund_id'   => $fund_id,
            'type'      => EnumTransactionType::IN,
            'category'  => EnumFundTransactionCategory::PROFIT,
        ])
          ->orderBy('created_at', 'DESC')
          ->paginate(10);

        return response($transactions, Response::HTTP_OK);
    }
}
