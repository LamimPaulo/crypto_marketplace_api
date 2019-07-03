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
                'limit_transaction_auto' => $obj->limit_transaction_auto,
                'brokerage_fee' => $obj->brokerage_fee,
                'is_referrable' => $obj->is_referrable,
                'referral_profit' => $obj->referral_profit,
                'is_active' => $obj->is_active,
                'is_allowed_sell_for_fiat' => $obj->is_allowed_sell_for_fiat,
                'is_allowed_buy_with_fiat' => $obj->is_allowed_buy_with_fiat,
                'nanotech_lqx_fee' => $obj->nanotech_lqx_fee,
                'nanotech_btc_fee' => $obj->nanotech_btc_fee,
                'masternode_fee' => $obj->masternode_fee,
                'type' => $obj->type
            ]);
        }
    }
}
