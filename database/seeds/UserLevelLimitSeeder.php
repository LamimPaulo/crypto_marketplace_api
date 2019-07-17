<?php

use Illuminate\Database\Seeder;
use App\Models\User\UserLevel;
use App\Models\Coin;
use App\Models\User\UserLevelLimit;
use App\Enum\EnumUserLevelLimitType;

class UserLevelLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = UserLevel::all();
        $coins = Coin::where([
            'is_crypto' => true,
            'is_wallet' => true,
            'is_active' => true,
        ])->get();

        foreach ($levels as $level) {
            foreach ($coins as $coin) {
                foreach (EnumUserLevelLimitType::TYPES as $k => $v) {
                    $levelLimit = UserLevelLimit::where([
                        'user_level_id' => $level->id,
                        'coin_id' => $coin->id,
                        'type' => $k,
                    ])->exists();

                    if (!$levelLimit) {
                        UserLevelLimit::create([
                            'user_level_id' => $level->id,
                            'coin_id' => $coin->id,
                            'type' => $k,
                            'limit' => $level->limit_btc_diary,
                            'limit_auto' => $level->limit_transaction_auto,
                        ]);
                    }
                }
            }
        }
    }
}
