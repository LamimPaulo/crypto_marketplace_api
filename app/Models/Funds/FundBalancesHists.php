<?php

namespace App\Models\Funds;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FundBalancesHists extends Model
{
    protected $table = 'fund_balances_hists';

    protected $fillable = [
        'fund_balance_id',
        'balance_free',
        'balance_blocked'
    ];

    public function fund()
    {
        return $this->belongsTo(FundBalances::class, 'fund_balance_id');
    }
}
