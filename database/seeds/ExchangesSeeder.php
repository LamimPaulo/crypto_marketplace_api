<?php

use App\Models\Exchange\Exchanges;
use Illuminate\Database\Seeder;

class ExchangesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $exchanges = [
            [
                'id' => 1,
                'name' => 'braziliex',
                'description' => 'Braziliex',
                'is_active' => 1,
                'is_certified' => 0,
            ], [
                'id' => 2,
                'name' => 'mercado',
                'description' => 'Mercado Bitcoin',
                'is_active' => 1,
                'is_certified' => 0,
            ], [
                'id' => 3,
                'name' => 'negociecoins',
                'description' => 'NegocieCoins',
                'is_active' => 1,
                'is_certified' => 0,
            ],
        ];

        foreach ($exchanges as $ex) {
            Exchanges::create($ex);
        }
    }
}
