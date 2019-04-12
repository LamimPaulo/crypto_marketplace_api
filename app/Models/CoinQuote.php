<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinQuote extends Model
{
    protected $fillable = [
        'coin_id',
        'quote_coin_id',
        'average_quote',
        'last_quote',
        'buy_quote',
        'sell_quote',
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function quote_coin()
    {
        return $this->belongsTo(Coin::class, 'quote_coin_id');
    }
}
