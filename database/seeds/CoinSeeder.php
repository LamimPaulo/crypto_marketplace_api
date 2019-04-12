<?php

use App\Models\Coin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CoinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Coin::truncate();

        $json = File::get("database/json/coins.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            Coin::create([
                'name' => $obj->name,
                'shortname' => $obj->shortname,
                'abbr' => $obj->abbr,
                'is_active' => $obj->is_active,
                'is_asset' => $obj->is_asset,
                'is_crypto' => $obj->is_crypto,
                'decimal' => $obj->decimal,
                'sell_tax' => $obj->sell_tax,
                'buy_tax' => $obj->buy_tax,
                'fee_low' => $obj->fee_low,
                'fee_avg' => $obj->fee_avg,
                'fee_high' => $obj->fee_high,
                'icon' => $obj->icon,
                'tx_explorer' => $obj->tx_explorer,
            ]);
        }
    }
}
