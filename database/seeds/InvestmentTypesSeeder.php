<?php

use Illuminate\Database\Seeder;

class InvestmentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'id' => 1,
                'type' => 'Nanotech',
                'brokerage_fee' => 1,
                'montly_return' => 5,

            ]
        ];
        foreach ($types as $type) {
            \App\Models\Investments\InvestmentType::create($type);
        }
    }
}
