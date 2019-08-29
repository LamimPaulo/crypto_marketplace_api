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
        Product::create([
            'id' => EnumUserLevel::CAD,
            'value' => 500,
            'product_type_id' => 1,
            'name' => 'Licença CAD',
        ]);
        Product::create([
            'id' => EnumUserLevel::INT_FREE,
            'value' => 50,
            'product_type_id' => 1,
            'name' => 'Free internacional',
        ]);
        Product::create([
            'id' => EnumUserLevel::INT_BASIC,
            'value' => 100,
            'product_type_id' => 1,
            'name' => 'Basic internacional',
        ]);
        Product::create([
            'id' => EnumUserLevel::INT_PRO,
            'value' => 200,
            'product_type_id' => 1,
            'name' => 'Pro internacional',
        ]);
        Product::create([
            'id' => EnumUserLevel::INT_GOLD,
            'value' => 300,
            'product_type_id' => 1,
            'name' => 'Gold internacional',
        ]);
        Product::create([
            'id' => EnumUserLevel::INT_INFINITY,
            'value' => 400,
            'product_type_id' => 1,
            'name' => 'Infinity internacional',
        ]);
        Product::create([
            'id' => EnumUserLevel::INT_CAD,
            'value' => 500,
            'product_type_id' => 1,
            'name' => 'CAD internacional',
        ]);
    }
}
