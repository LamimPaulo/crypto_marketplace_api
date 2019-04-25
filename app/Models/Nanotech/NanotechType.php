<?php

namespace App\Models\Nanotech;

use App\Models\Coin;
use Illuminate\Database\Eloquent\Model;

class NanotechType extends Model
{
    protected $fillable = [
        'type',
        'coin_id',
        'montly_return'
    ];

    public function investments()
    {
        return $this->hasMany(Nanotech::class, 'type_id');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function profit_percents()
    {
        return $this->hasMany(NanotechProfitPercent::class, 'type_id');
    }
}
