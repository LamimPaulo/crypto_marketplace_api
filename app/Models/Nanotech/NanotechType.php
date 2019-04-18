<?php

namespace App\Models\Nanotech;

use Illuminate\Database\Eloquent\Model;

class NanotechType extends Model
{
    protected $fillable = [
        'type',
        'brokerage_fee',
        'montly_return'
    ];

    public function investments()
    {
        $this->hasMany(Nanotech::class, 'type_id');
    }

    public function profit_percents()
    {
        $this->hasMany(NanotechProfitPercent::class, 'type_id');
    }
}
