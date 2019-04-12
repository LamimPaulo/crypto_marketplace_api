<?php

use App\Models\User\UserLevel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UserLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserLevel::truncate();

        $json = File::get("database/json/user_levels.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            UserLevel::create([
                'id' => $obj->id,
                'product_id' => $obj->product_id,
                'name' => $obj->name,
                'limit_btc_diary' => $obj->limit_btc_diary,
                'limit_brl_diary' => $obj->limit_brl_diary,
                'limit_usd_diary' => $obj->limit_usd_diary,
                'limit_transaction_auto' => $obj->limit_transaction_auto,
                'brokerage_fee' => $obj->brokerage_fee,
                'is_referrable' => $obj->is_referrable,
                'referral_profit' => $obj->referral_profit,
                'is_gateway_elegible' => $obj->is_gateway_elegible,
                'gateway_tax' => $obj->gateway_tax,
                'is_gateway_mmn_elegible' => $obj->is_gateway_mmn_elegible,
                'gateway_mmn_tax' => $obj->gateway_mmn_tax,
                'is_card_elegible' => $obj->is_card_elegible,
                'is_active' => $obj->is_active,
            ]);
        }
    }
}
