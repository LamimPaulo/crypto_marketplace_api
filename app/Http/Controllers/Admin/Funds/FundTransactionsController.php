<?php

namespace App\Http\Controllers\Admin\Funds;

use App\Enum\EnumFundTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Http\Controllers\Controller;
use App\Models\Funds\FundBalances;
use App\Models\Funds\FundBalancesHists;
use App\Models\Funds\FundTransaction;
use Carbon\Carbon;
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
            'fund_id' => $fund_id,
            'type' => EnumTransactionType::IN,
            'category' => EnumFundTransactionCategory::PROFIT,
        ])
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return response($transactions, Response::HTTP_OK);
    }

    public function profitsCommand()
    {
        $balances = FundBalances::with('fund')
            ->where('created_at', '<', Carbon::now()->startOfMonth())
            ->where('end_date', '>', Carbon::now()->startOfMonth())
            ->where('balance_blocked', '>', 0)
            ->get();

        foreach ($balances as $balance) {
            $profits = FundTransaction::where([
                'fund_id' => $balance->fund_id,
                'user_id' => $balance->user_id,
                'type' => EnumTransactionType::IN,
                'category' => EnumFundTransactionCategory::PROFIT,
            ])
                ->where('created_at', '>', Carbon::now()->startOfMonth())
                ->first();

            if (!$profits) {
                $profit_value = $balance->balance_blocked * $balance->fund->monthly_profit / 100;

                FundTransaction::create([
                    'user_id' => $balance->user_id,
                    'fund_id' => $balance->fund_id,
                    'coin_id' => $balance->fund->coin_id,
                    'value' => $profit_value,
                    'tax' => 0,
                    'profit_percent' => $balance->fund->monthly_profit,
                    'type' => EnumTransactionType::IN,
                    'category' => EnumFundTransactionCategory::PROFIT,
                    'status' => EnumTransactionsStatus::SUCCESS,
                ]);

                FundBalances::increments_free($balance, $profit_value);
                $newBalance = FundBalances::find($balance->id);
                FundBalancesHists::create([
                    'fund_balance_id' => $balance->id,
                    'balance_free' => $newBalance->balance_free,
                    'balance_blocked' => $newBalance->balance_blocked
                ]);
            }
        }
    }
}
