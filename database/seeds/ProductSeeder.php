<?php

use App\Enum\EnumUserLevel;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::create([
            'id' => EnumUserLevel::FREE,
            'value' => 50,
            'product_type_id' => 1,
            'name' => 'Free',
        ]);
        Product::create([
            'id' => EnumUserLevel::BASIC,
            'value' => 100,
            'product_type_id' => 1,
            'name' => 'Basic',
        ]);
        Product::create([
            'id' => EnumUserLevel::PRO,
            'value' => 200,
            'product_type_id' => 1,
            'name' => 'Pro',
        ]);
        Product::create([
            'id' => EnumUserLevel::GOLD,
            'value' => 300,
            'product_type_id' => 1,
            'name' => 'Gold',
        ]);
        Product::create([
            'id' => EnumUserLevel::INFINITY,
            'value' => 400,
            'product_type_id' => 1,
            'name' => 'Infinity',
        ]);
    }
}
