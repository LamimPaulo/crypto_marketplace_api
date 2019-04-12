<?php

namespace App\Models\Investments;

use App\User;
use Illuminate\Database\Eloquent\Model;

class InvestmentOperation extends Model
{
    protected $fillable = [
        'user_id',
        'investment_id',
        'amount',
        'brokerage_fee',
        'brokerage_fee_percentage',
        'profit_percent',
        'type',
        'status'
    ];

    public function investment()
    {
        $this->belongsTo(Investment::class, 'investment_id');
    }

    public function user()
    {
        $this->belongsTo(User::class, 'user_id');
    }
}
