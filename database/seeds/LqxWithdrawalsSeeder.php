<?php

use Illuminate\Database\Seeder;
use App\Models\LqxWithdrawal;

class LqxWithdrawalsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!LqxWithdrawal::first()) {
            $dates = [
                [
                    'date' => '2019-07-31',
                    'is_executed' => true,
                    'percent' => 25,
                ], [
                    'date' => '2019-10-31',
                    'is_executed' => false,
                    'percent' => 25,
                ], [
                    'date' => '2020-01-31',
                    'is_executed' => false,
                    'percent' => 25,
                ], [
                    'date' => '2020-04-30',
                    'is_executed' => false,
                    'percent' => 25,
                ],
            ];

            foreach ($dates as $date) {
                LqxWithdrawal::create($date);
            }
        }
    }
}
