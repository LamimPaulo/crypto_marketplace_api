<?php

namespace App\Models\Investments;

use Illuminate\Database\Eloquent\Model;

class InvestmentProfitPercent extends Model
{
    protected $fillable = [
        'percent',
        'day',
        'type_id'
    ];

    public function investment_type()
    {
        return $this->belongsTo(InvestmentType::class, 'type_id');
    }
}
