<?php

use Illuminate\Database\Seeder;

class MiningPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $miningPlans = [
            [
                'id' => 1,
                'name' => 'Mineração BTC',
                'ths_total' => 5000,
                'ths_quota' => 5000,
                'ths_quota_price' => 150,
                'ths_quota_price_type' => 1,
                'profit' => 30,
                'profit_type' => \App\Enum\EnumMiningProfitType::PERCENT,
                'profit_payout' => 0.03,
            ],
        ];
        foreach ($miningPlans as $plan) {
            \App\Models\Mining\MiningPlan::create($plan);
        }
    }
}
