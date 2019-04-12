<?php

use App\Models\CoinPair;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CoinPairsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CoinPair::truncate();

        $json = File::get("database/json/coin_pairs.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            CoinPair::create([
                'id' => $obj->id,
                'name' => $obj->name,
                'base_coin_id' => $obj->base_coin_id,
                'quote_coin_id' => $obj->quote_coin_id,
                'min_trade_amount' => $obj->min_trade_amount,
                'is_asset_option' => $obj->is_asset_option,
                'is_trade_option' => $obj->is_trade_option,
                'description' => $obj->description,
            ]);
        }
    }
}
