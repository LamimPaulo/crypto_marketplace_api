<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDashboard extends Model
{
    protected $fillable = [
        'id',
        'nanotech_lqx',
        'nanotech_btc',
        'masternode',
        'users',
        'incomplete_users',
        'unverified_docs',
        'levels',
        'levels_sold',
        'levels_lqx',
        'levels_lqx_sold',
        'deposits',
        'deposits_amount',
        'deposits_pending',
        'deposits_pending_amount',
        'deposits_rejected',
        'deposits_rejected_amount',
        'deposits_paid',
        'deposits_paid_amount',
        'withdrawals',
        'withdrawals_amount',
        'withdrawals_pending',
        'withdrawals_pending_amount',
        'withdrawals_paid',
        'withdrawals_paid_amount',
        'withdrawals_processing',
        'withdrawals_processing_amount',
        'withdrawals_reversed',
        'withdrawals_reversed_amount',
        'balance_brl',
        'crypto_operations'
    ];

    public function getCryptoOperationsAttribute($value)
    {
        return json_decode($value);
    }
}
