<?php

namespace App\Models\Funds;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FundQuoteHists extends Model
{
    protected $fillable = [
        'user_id',
        'fund_id',
        'quote',
        'value',
        'amount'
    ];

    public function fund()
    {
        return $this->belongsTo(Funds::class, 'fund_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
