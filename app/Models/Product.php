<?php

namespace App\Models;

use App\Models\User\UserLevel;
use App\Services\ConversorService;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed value
 * @property mixed value_lqx
 */
class Product extends Model
{
    protected $fillable = [
        'product_type_id',
        'value',
        'value_lqx',
        'name',
        'description',
        'is_active'
    ];

    protected $appends = [
        'brlValue',
        'lqxValue'
    ];

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function level()
    {
        return $this->hasOne(UserLevel::class, 'product_id');
    }

    public function getBrlValueAttribute()
    {
        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }

    public function getLqxValueAttribute()
    {
        return sprintf('%.5f', $this->value_lqx);
    }
}
