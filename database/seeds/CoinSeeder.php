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
                'id' => $obj->id,
                'name' => $obj->name,
                'shortname' => $obj->shortname,
                'abbr' => $obj->abbr,
                'decimal' => $obj->decimal,
                'sell_tax' => $obj->sell_tax,
                'buy_tax' => $obj->buy_tax,
                'fee_low' => $obj->fee_low,
                'fee_avg' => $obj->fee_avg,
                'fee_high' => $obj->fee_high,
                'is_active' => $obj->is_active,
                'is_crypto' => $obj->is_crypto,
                'icon' => $obj->icon,
                'created_at' => $obj->created_at,
                'updated_at' => $obj->updated_at,
                'tx_explorer' => $obj->tx_explorer,
                'wallet_order' => $obj->wallet_order,
                'core_limit_balance' => $obj->core_limit_balance,
                'core_limit_percent' => $obj->core_limit_percent,
                'withdrawal_address' => $obj->withdrawal_address,
                'is_wallet' => $obj->is_wallet,
                'is_masternode' => $obj->is_masternode,
                'core_balance' => $obj->core_balance,
                'core_status' => $obj->core_status,
            ]);
        }
    }
}
