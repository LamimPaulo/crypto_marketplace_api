<?php

namespace App\Models\Investments;

use Illuminate\Database\Eloquent\Model;

class InvestmentType extends Model
{
    protected $fillable = [
        'type',
        'brokerage_fee',
        'montly_return'
    ];

    public function investments()
    {
        $this->hasMany(Investment::class, 'type_id');
    }

    public function profit_percents()
    {
        $this->hasMany(InvestmentProfitPercent::class, 'type_id');
    }
}
