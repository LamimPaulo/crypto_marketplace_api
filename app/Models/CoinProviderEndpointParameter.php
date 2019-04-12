<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinProviderEndpointParameter extends Model
{
    protected $fillable = ['parameter', 'required', 'decription', 'endpoint_id'];

    public function endpoint()
    {
        return $this->belongsTo(CoinProviderEndpoint::class, 'endpoint_id');
    }
}
