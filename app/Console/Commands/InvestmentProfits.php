<?php

namespace App\Console\Commands;

use App\Enum\EnumInvestmentOperationStatus;
use App\Enum\EnumInvestmentOperationType;
use App\Models\Investments\Investment;
use App\Models\Investments\InvestmentOperation;
use App\Models\Investments\InvestmentProfitPercent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class InvestmentProfits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'investment:profits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera os lucros diários para os investidores';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->generate();
    }

    private function generate()
    {
        $investments = Investment::all();

        foreach ($investments as $investment) {
            $profits_today = InvestmentOperation::where('type', EnumInvestmentOperationType::PROFIT)
                                                ->where('investment_id', $investment->id)
                                                ->whereDate('created_at', '=', Carbon::today()->toDateString())
                                                ->where('user_id', $investment->user_id)->count();

            if ($profits_today == 0) {
                $profitPercentToday = InvestmentProfitPercent::where('day', date("Y-m-d"))
                                                             ->where('type_id', $investment->type_id)->first()->percent;

                $total_profit = ($profitPercentToday * $investment->amount) / 100;

                if($total_profit>0) {
                    InvestmentOperation::create([
                        'user_id' => $investment->user_id,
                        'investment_id' => $investment->id,
                        'amount' => $total_profit,
                        'profit_percentage' => $profitPercentToday,
                        'status' => EnumInvestmentOperationStatus::SUCCESS,
                        'type' => EnumInvestmentOperationType::PROFIT,
                    ]);
                }
            }
        }
    }
}
