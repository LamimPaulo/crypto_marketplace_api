<?php

use Illuminate\Database\Seeder;

class SysConfigsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configs = [
            [
                'id' => 1,
                'buy_tax' => 2,
                'sell_tax' => 1,
                'deposit_min_valor' => 120,
                'send_min_btc' => 0.0004,
                'ip' => '192.168.10.1',
                'secret' => 'root',
                'time_gateway' => 30,
                'investiment_return' => 5,
            ]
        ];
        foreach ($configs as $cfg) {
            \App\Models\SysConfig::create($cfg);
        }
    }
}
