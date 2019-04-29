<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDashboard extends Model
{
    protected $fillable = [
        'docs_unverified',
        'incomplete_users',
        'complete_users',
        'total_users',
        'withdrawals_pending_count',
        'withdrawals_pending_amount',
        'withdrawals_success_count',
        'withdrawals_success_amount',
        'deposits_pending_count',
        'deposits_pending_amount',
        'deposits_success_count',
        'deposits_success_amount',
        'levels_amount',
        'withdrawals_nanotech_btc',
        'withdrawals_nanotech_btc_amount',
        'withdrawals_nanotech_lqx',
        'withdrawals_nanotech_lqx_amount'
    ];
}
