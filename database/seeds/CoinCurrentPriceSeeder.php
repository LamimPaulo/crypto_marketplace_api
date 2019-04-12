<?php

use App\Models\CoinCurrentPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CoinCurrentPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CoinCurrentPrice::truncate();

        $json = File::get("database/json/coin_current_prices.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            CoinCurrentPrice::create([
                'id' => $obj->id,
                'provider_id' => $obj->provider_id,
                'coin_id' => $obj->coin_id,
                'symbol' => $obj->symbol,
                'price' => $obj->price,
            ]);
        }
    }
}
