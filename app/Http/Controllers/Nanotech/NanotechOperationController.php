<?php

namespace App\Http\Controllers\Nanotech;

use App\Enum\EnumNanotechOperationStatus;
use App\Enum\EnumNanotechOperationType;
use App\Http\Controllers\Controller;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Nanotech\NanotechProfitPercent;
use Carbon\Carbon;

class NanotechOperationController extends Controller
{
    public function profits()
    {
        $investments = Nanotech::all();

        foreach ($investments as $investment) {
            $profits_today = NanotechOperation::where('type', EnumNanotechOperationType::PROFIT)
                ->where('investment_id', $investment->id)
                ->whereDate('created_at', '=', Carbon::today()->toDateString())
                ->where('user_id', $investment->user_id)->count();

            if ($profits_today == 0) {
                $profitPercentToday = NanotechProfitPercent::where('day', date("Y-m-d"))
                    ->where('type_id', $investment->type_id)->first()->percent;

                $total_profit = ($profitPercentToday * $investment->amount) / 100;

                if ($total_profit > 0) {
                    NanotechOperation::create([
                        'user_id' => $investment->user_id,
                        'investment_id' => $investment->id,
                        'amount' => $total_profit,
                        'profit_percentage' => $profitPercentToday,
                        'status' => EnumNanotechOperationStatus::SUCCESS,
                        'type' => EnumNanotechOperationType::PROFIT,
                    ]);
                }
            }
        }
    }

    public function list_pendingOperations()
    {
        return $pendingList = NanotechOperation::join('nanotech as nano', 'nano.id', '=', 'nanotech_operations.investment_id')
        // ->where('nano.coin_id', 1)
        ->where('nanotech_operations.user_id', auth()->user()->id)
        ->where('nanotech_operations.status', EnumNanotechOperationStatus::PENDING)
        ->where('nanotech_operations.type', '>=', EnumNanotechOperationType::WITHDRAWAL)
        ->where('nanotech_operations.type', '<=' , EnumNanotechOperationType::PROFIT_WITHDRAWAL)
        ->orderBy('nanotech_operations.created_at', 'ASC')
        ->get();
    }
}

// 


