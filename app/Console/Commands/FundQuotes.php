<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\CoinCurrentPrice;
use App\Models\CoinQuote;
use App\Models\Funds\FundCoins;
use App\Models\Funds\FundOrders;
use App\Models\Funds\Funds;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FundQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:fundquotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Index Fund Quotes';

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
     * @throws \Exception
     */
    public function handle()
    {
        $this->updateFundPrices();
    }


    public function updateFundPrices()
    {
        $funds = Funds::with('coins')->where('is_active', true)->get();

        foreach ($funds as $fund) {
            $start_value = $fund->start_price;
            $new_value = 0;

            foreach ($fund->coins as $coin) {
                $fr_current_brl = $start_value * ($coin->percent / 100);
                $fr_new_brl = $fr_current_brl + ($fr_current_brl * ($coin->current_price / 100));
                $new_value += $fr_new_brl;
            }
            $fund->value = $new_value;
            $fund->save();
        }
    }
}
