<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinPrice extends Model
{
    protected $fillable = [
        'provider_id',
        'coin_id',
        'symbol',
        'price_change',
        'price_change_percent',
        'prev_close_price',
        'last_price',
        'bid_price',
        'ask_price',
        'open_price',
        'high_price',
        'low_price',
        'opentime'
    ];

    public function provider()
    {
        return $this->belongsTo(CoinProvider::class, 'provider_id');
    }
}
