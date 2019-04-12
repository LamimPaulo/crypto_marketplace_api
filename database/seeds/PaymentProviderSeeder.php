<?php

use App\Models\PaymentProvider;
use Illuminate\Database\Seeder;

class PaymentProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $providers = [
            [
                'id' => 1,
                'name' => 'Conta Bancária',
                'email' => 'vendasnavi@hotmail.com'
            ], [
                'id' => 2,
                'name' => 'Paypal',
                'email' => 'vendasnavi@hotmail.com'
            ], [
                'id' => 3,
                'name' => 'Neteller',
                'email' => 'vendasnavi@hotmail.com'
            ]
        ];
        foreach ($providers as $p) {
            PaymentProvider::create($p);
        }
    }
}
