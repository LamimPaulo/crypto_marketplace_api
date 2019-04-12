<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinPair extends Model
{
    protected $fillable = [
        'name',
        'base_coin_id',
        'quote_coin_id',
        'min_trade_amount',
        'is_asset_option',
        'is_trade_option',
        'description',
    ];

    public function provider()
    {
        return $this->belongsToMany(CoinProvider::class, 'coin_pair_provider', 'pair_id');
    }
}
