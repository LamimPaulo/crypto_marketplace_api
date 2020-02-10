<?php

namespace App\Console\Commands;

use App\Enum\EnumNanotechOperationStatus;
use App\Enum\EnumNanotechOperationType;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechOperation;
use App\Models\Nanotech\NanotechProfitPercent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NanotechProfitsSub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nanotechsub:profits {--day=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gera os lucros diários passados para os investidores';

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
        if (env("APP_ENV") == 'local') {
            $days = $this->option('day');
            $this->generate($days);
        }
    }

    private function generate($days)
    {
        $investments = Nanotech::all();

        foreach ($investments as $investment) {
            $profits_today = NanotechOperation::where('type', EnumNanotechOperationType::PROFIT)
                ->where('investment_id', $investment->id)
                ->whereDate('created_at', '=', Carbon::now()->subDays($days[0])->toDateString())
                ->where('user_id', $investment->user_id)->count();

            if ($profits_today == 0) {
                $profitPercentToday = NanotechProfitPercent::where('day', Carbon::now()->subDays($days[0])->format("Y-m-d"))
                    ->where('type_id', $investment->type_id)->first()->percent;

                $total_profit = ($profitPercentToday * $investment->amount) / 100;

                if ($total_profit > 0) {
                    NanotechOperation::create([
                        'user_id' => $investment->user_id,
                        'investment_id' => $investment->id,
                        'amount' => $total_profit,
                        'profit_percent' => $profitPercentToday,
                        'status' => EnumNanotechOperationStatus::SUCCESS,
                        'type' => EnumNanotechOperationType::PROFIT,
                        'created_at' => Carbon::now()->subDays($days[0])->toDateString(),
                    ]);
                }
            }
        }
    }
}
