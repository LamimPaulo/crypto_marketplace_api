<?php

namespace App\Models\Funds;

use App\Models\Coin;
use Illuminate\Database\Eloquent\Model;

class FundCoins extends Model
{
    protected $fillable = [
        'fund_id',
        'coin_id',
        'percent'
    ];

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function fund()
    {
        return $this->belongsTo(Funds::class, 'fund_id');
    }
}
