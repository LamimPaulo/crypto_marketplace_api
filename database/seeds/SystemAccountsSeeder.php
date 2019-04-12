<?php

use Illuminate\Database\Seeder;

class SystemAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $accounts = [
            [
                'id' => 1,
                'bank_id' => 141,
                'provider_id' => 1,
                'agency' => 3392,
                'account' => 2605,
                'agency_digit' => '',
                'account_digit' => 6,
                'name' => 'NAVI Serviços Digitais LTDA',
                'document' => '20.924.974/0001-79',
                'email' => '',
                'type' => \App\Enum\EnumAccountType::BANK,
                'observation' => 'Conta PJ - OP 003',
                'is_active' => 1,
            ]
        ];
        foreach ($accounts as $acc) {
            \App\Models\System\SystemAccount::create($acc);
        }
    }
}
