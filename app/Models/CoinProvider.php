<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinProvider extends Model
{
    protected $fillable = [
        'name',
        'endpoint',
        'comission',
        'comission_type',
        'service_key',
        'is_active',
    ];

    public function pairs()
    {
        return $this->belongsToMany(CoinPair::class, 'coin_pair_provider', 'provider_id');
    }

    public function prices()
    {
        return $this->hasMany(CoinPrice::class, 'provider_id');
    }

    public function current_prices()
    {
        return $this->hasMany(CoinCurrentPrice::class, 'provider_id');
    }

    public function endpoints()
    {
        return $this->hasMany(CoinProviderEndpoint::class, 'provider_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'provider_id');
    }
}
