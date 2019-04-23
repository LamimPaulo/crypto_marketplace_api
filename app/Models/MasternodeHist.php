<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasternodeHist extends Model
{
    //
    protected $fillable = [
        'coin_id',
        'roi',
        'daily_return',
        'daily_return_btc'
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function masternode()
    {
        return $this->belongsTo(Masternode::class, 'coin_id','coin_id');
    }

}
