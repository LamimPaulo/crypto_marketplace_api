<?php

namespace App\Models;

use App\Models\User\UserLevel;
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
        'bonus_percent',
        'description',
        'is_active'
    ];

    protected $appends = [
        'brlValue',
        'lqxValue',
        'value_usd'
    ];

    public function type()
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function level()
    {
        return $this->belongsTo(UserLevel::class, 'product_id');
    }

    public function getBrlValueAttribute()
    {
        return 'R$ ' . number_format($this->value, 2, ',', '.');
    }

    public function getValueUsdAttribute()
    {
        $quote = CoinQuote::where([
            'coin_id' => Coin::getByAbbr("USD")->id,
            'quote_coin_id' => Coin::getByAbbr("BRL")->id
        ])->first();

        if ($this->value > 0) {
            return number_format($this->value / $quote->average_quote, 2, '.', '');
        }

        return $this->value;
    }

    public function getLqxValueAttribute()
    {
        return sprintf('%.5f', $this->value_lqx);
    }
}
