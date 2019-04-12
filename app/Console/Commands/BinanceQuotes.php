<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\CoinCurrentPrice;
use App\Models\CoinPrice;
use App\Models\CoinProvider;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BinanceQuotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:binancequote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Binance Quotes';

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
        $this->updateCurrentPrices();
        $this->updatePrices();
    }

    public function updatePrices()
    {
        try {

            $api = new \GuzzleHttp\Client(['http_errors' => false]);
            $coins = Coin::whereNotIn('abbr', ['BRL', 'USDT', 'BTC'])->get();
            $provider = CoinProvider::first();

            $response = $api->get("{$provider->endpoint}/api/v1/ticker/24hr?symbol=BTCUSDT");
            $result = json_decode($response->getBody()->getContents());

            $coin = CoinPrice::firstOrNew(['provider_id' => $provider->id, 'symbol' => 'BTC', 'coin_id' => 1]);
            $coin->price_change = $result->priceChange;
            $coin->price_change_percent = $result->priceChangePercent;
            $coin->prev_close_price = $result->prevClosePrice;
            $coin->last_price = $result->lastPrice;
            $coin->bid_price = $result->bidPrice;
            $coin->ask_price = $result->askPrice;
            $coin->open_price = $result->openPrice;
            $coin->high_price = $result->highPrice;
            $coin->low_price = $result->lowPrice;
            $coin->coin_id = 1;
            $coin->opentime = Carbon::createFromTimestamp($result->openTime / 1000)->toDateTimeString();
            $coin->save();

            foreach ($coins as $c) {
                $response = $api->get("{$provider->endpoint}/api/v1/ticker/24hr?symbol={$c->abbr}BTC");
                $statuscode = $response->getStatusCode();

                if (200 === $statuscode) {
                    $result = json_decode($response->getBody()->getContents());
                    $coin = CoinPrice::firstOrNew(['provider_id' => $provider->id, 'symbol' => $c->abbr, 'coin_id' => $c->id]);
                    $coin->price_change = $result->priceChange;
                    $coin->price_change_percent = $result->priceChangePercent;
                    $coin->prev_close_price = $result->prevClosePrice;
                    $coin->last_price = $result->lastPrice;
                    $coin->bid_price = $result->bidPrice;
                    $coin->ask_price = $result->askPrice;
                    $coin->open_price = $result->openPrice;
                    $coin->high_price = $result->highPrice;
                    $coin->low_price = $result->lowPrice;
                    $coin->coin_id = $c->id;
                    $coin->opentime = Carbon::createFromTimestamp($result->openTime / 1000)->toDateTimeString();
                    $coin->save();
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function updateCurrentPrices()
    {

        try {
            $api = new \GuzzleHttp\Client(['http_errors' => false]);
            $provider = CoinProvider::first();

            $response = $api->get("{$provider->endpoint}/api/v3/avgPrice?symbol=BTCUSDT");
            $result = json_decode($response->getBody()->getContents());
            $coin = CoinCurrentPrice::firstOrNew(['provider_id' => $provider->id, 'symbol' => 'BTC', 'coin_id' => 1]);
            $coin->price = $result->price;
            $coin->save();


            $coins = Coin::whereNotIn('abbr', ['BRL', 'USDT'])->where('is_asset', true)->get();

            foreach ($coins as $s) {
                $sym = $s->abbr == 'BTC' ? 'USDT' : 'BTC';
                $response = $api->get("{$provider->endpoint}/api/v3/avgPrice?symbol={$s->abbr}{$sym}");
                $statuscode = $response->getStatusCode();

                if (200 === $statuscode) {
                    $result = json_decode($response->getBody()->getContents());

                    $coin = CoinCurrentPrice::firstOrNew(['provider_id' => $provider->id, 'symbol' => $s->abbr, 'coin_id' => $s->id]);
                    $coin->price = $result->price;
                    $coin->save();
                }
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }
}
