<?php

namespace App\Models\Exchange;

use Illuminate\Database\Eloquent\Model;

class ExchangeTax extends Model
{
    protected $fillable = [
        'exchange_id',
        'coin_id',
        'type',
        'calc_type',
        'value'
    ];

    public function exchange()
    {
        return $this->belongsTo(Exchanges::class, 'exchange_id');
    }
}
