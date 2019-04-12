<?php

use Illuminate\Database\Seeder;
use App\Models\CoinPrice;
use Illuminate\Support\Facades\File;

class CoinPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CoinPrice::truncate();

        $json = File::get("database/json/coin_prices.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            CoinPrice::create([
                'id' => $obj->id,
                'provider_id' => $obj->provider_id,
                'coin_id' => $obj->coin_id,
                'symbol' => $obj->symbol,
                'price_change' => $obj->price_change,
                'price_change_percent' => $obj->price_change_percent,
                'prev_close_price' => $obj->prev_close_price,
                'last_price' => $obj->last_price,
                'bid_price' => $obj->bid_price,
                'ask_price' => $obj->ask_price,
                'open_price' => $obj->open_price,
                'high_price' => $obj->high_price,
                'low_price' => $obj->low_price,
                'opentime' => $obj->opentime,
            ]);
        }
    }
}
