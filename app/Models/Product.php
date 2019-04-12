<?php

namespace App\Models;

use App\Models\User\UserLevel;
use App\Services\ConversorService;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed value
 * @property mixed value_usd
 */
class Product extends Model
{
    protected $fillable = ['product_type_id', 'value', 'value_usd', 'name', 'description', 'is_active'];

    protected $appends = ['brlValue', 'usdValue', 'btcValue'];

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

    public function getUsdValueAttribute()
    {
        return '$ ' . sprintf('%.2f', $this->value_usd);
    }

    public function getBtcValueAttribute()
    {
        $conversor = new ConversorService();
        $currentValue = $conversor::BRL2BTCMIN($this->value);
        return sprintf('%.8f', $currentValue['amount']);
    }
}
