<?php

namespace App\Models\Funds;

use App\Models\Coin;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed price
 */
class Funds extends Model
{
    protected $fillable = [
        'name',
        'buy_tax',
        'redemption_tax',
        'early_redemption_tax',
        'coin_id',
        'price',
        'monthly_profit',
        'validity',//meses
        'is_active',
        'description',
    ];

    protected $appends = [
        'priceLocal',
    ];


    //Appends
    public function getPriceLocalAttribute()
    {
        if ($this->coin_id == 2) {
            return number_format($this->price, 2, '.', ',');
        }
        return sprintf("%.5f", $this->price);
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function coins()
    {
        return $this->hasMany(FundCoins::class, 'fund_id');
    }

    public function coins_percent()
    {
        return $this->hasMany(FundCoins::class, 'fund_id')->percent;
    }
}
