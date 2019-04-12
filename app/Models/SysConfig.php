<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysConfig extends Model
{
    protected $fillable = [
        'id',
        'buy_tax',
        'sell_tax',
        'deposit_min_valor',
        'ip',
        'secret',
        'time_gateway',
        'send_min_btc',
        'investiment_return'
    ];

}
