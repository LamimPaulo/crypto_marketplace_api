<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\CoinPair;
use Illuminate\Console\Command;

class GeneratePairsBinance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:binancepairs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate New Binance Pairs';

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
        $this->generatePairs();
    }

    private function generatePairs()
    {
        $coins = Coin::whereNotIn('abbr', ['BRL', 'USDT', 'BTC'])->where('is_asset', true)->where('is_active', true)->get();
        $btc = Coin::getByAbbr('BTC');

        foreach ($coins as $coin) {
            $pair = CoinPair::firstOrNew([
                'name' => $coin->abbr.$btc->abbr,
                'base_coin_id' => $coin->id,
                'quote_coin_id' => $btc->id,
            ]);

            $pair->min_trade_amount = 0.0011;
            $pair->is_asset_option = 1;
            $pair->is_trade_option = 0;
            $pair->save();
        }
    }
}
