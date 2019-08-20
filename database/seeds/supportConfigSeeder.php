<?php

use Illuminate\Database\Seeder;
use App\Models\SupportConfig;

class supportConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        SupportConfig::create([
            'days_off' => '0,6',
            'fri_close_time' => '20:00:00',
            'mon_opening_time' => '08:00:00'
        ]);
    }
}
