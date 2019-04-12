<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function products()
    {
        return $this->hasMany(Product::class, 'product_type_id');
    }
}
