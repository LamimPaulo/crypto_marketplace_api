<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserBalanceHist extends Model
{
    protected $table = 'user_balance_hists';

    protected $fillable = [
        'wallet_id',
        'user_id',
        'coin_id',
        'address',
        'transaction_id',
        'amount',
        'fee',
        'tax',
        'balance',
        'type',
    ];

}
