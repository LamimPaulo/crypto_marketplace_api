<?php

use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ProductType::create([
            'id' => 1,
            'name' => 'Níveis',
            'is_active' => 1
        ]);
    }
}
