<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatewayStatus extends Model
{
    protected $fillable = [
        'gateway_id',
        'status'
    ];

    protected $hidden = [
        'id',
        'gateway_id'
    ];

    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'gateway_id');
    }
}
