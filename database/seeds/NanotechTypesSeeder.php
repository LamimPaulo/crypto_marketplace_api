<?php

use Illuminate\Database\Seeder;

class NanotechTypesSeeder extends Seeder
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
                'type' => 'Nanotech LQX',
                'montly_return' => 1,
                'coin_id' => 3
            ], [
                'id' => 2,
                'type' => 'Nanotech BTC',
                'montly_return' => 2,
                'coin_id' => 1
            ], [
                'id' => 3,
                'type' => 'Masternodes',
                'montly_return' => 3,
                'coin_id' => 3
            ],
        ];
        foreach ($types as $type) {
            \App\Models\Nanotech\NanotechType::create($type);
        }
    }
}
