<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinQuoteHist extends Model
{
    protected $table = 'coin_quotes_hist';

    protected $fillable = [
        'coin_id',
        'quote_coin_id',
        'average_quote',
        'buy_quote',
        'sell_quote',
    ];
}
