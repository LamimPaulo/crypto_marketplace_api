<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinProviderEndpoint extends Model
{
    protected $fillable = [
        'endpoint',
        'name',
        'method',
        'description',
        'provider_id'
    ];

    public function provider()
    {
        return $this->belongsTo(CoinProvider::class, 'provider_id');
    }

    public function parameters()
    {
        return $this->hasMany(CoinProviderEndpointParameter::class, 'endpoint_id');
    }
}
