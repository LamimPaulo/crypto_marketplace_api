<?php

namespace App\Models\Exchange;

use Illuminate\Database\Eloquent\Model;

class Exchanges extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'is_certified'
    ];

    public function taxes()
    {
        return $this->hasMany(ExchangeTax::class, 'exchange_id');
    }

    public function comission()
    {
        return $this->hasOne(ExchangeTax::class, 'exchange_id')->where('type', 1);
    }
}
