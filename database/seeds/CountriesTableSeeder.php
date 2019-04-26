<?php

use Illuminate\Database\Seeder;
use App\Models\Country;
use Illuminate\Support\Facades\File;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Country::truncate();

        $json = File::get("database/json/countries.json");
        $data = json_decode($json);
        foreach ($data as $obj) {
            Country::create([
                'id' => $obj->id,
                'name' => $obj->name,
                'dial_code' => $obj->dial_code,
                'code' => $obj->code,
                'timezone' => $obj->timezone
            ]);
        }
    }
}
