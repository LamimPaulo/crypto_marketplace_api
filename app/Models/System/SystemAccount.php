<?php

namespace App\Models\System;

use App\Models\Bank;
use App\Models\PaymentProvider;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class SystemAccount extends Model
{
    protected $fillable = [
        'bank_id',
        'provider_id',
        'agency',
        'account',
        'agency_digit',
        'account_digit',
        'name',
        'document',
        'email',
        'type',
        'category',
        'observation',
        'is_active'
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function deposits()
    {
        return $this->belongsTo(Transaction::class, 'system_account_id');
    }

    public function provider()
    {
        return $this->belongsTo(PaymentProvider::class, 'provider_id');
    }

}
