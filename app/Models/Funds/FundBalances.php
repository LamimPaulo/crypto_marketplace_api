<?php

namespace App\Models\Funds;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FundBalances extends Model
{
    protected $table = 'fund_balances';

    protected $fillable = [
        'user_id',
        'fund_id',
        'balance',
        'type'
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
