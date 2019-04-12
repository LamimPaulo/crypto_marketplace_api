<?php

namespace App\Models\Funds;

use App\Models\Coin;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed current_price
 */
class FundCoins extends Model
{
    protected $fillable = [
        'fund_id',
        'coin_id',
        'percent',
        'price',
        'current_price',
        'amount',
    ];

    protected $appends = ['percentChange','amountLocal'];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function fund()
    {
        return $this->belongsTo(Funds::class, 'fund_id');
    }

    public function getPercentChangeAttribute()
    {
        return sprintf("%.3f", $this->current_price);
    }

    public function getAmountLocalAttribute()
    {
        if($this->coin_id==2){
            return number_format($this->amount,2,',','.');
        }else{
            return sprintf("%.8f", $this->amount);
        }
    }
}
