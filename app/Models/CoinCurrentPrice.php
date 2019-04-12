<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinCurrentPrice extends Model
{
    protected $fillable = ['provider_id', 'symbol', 'price', 'coin_id'];

    public function provider()
    {
        return $this->belongsTo(CoinProvider::class, 'provider_id');
    }
}
