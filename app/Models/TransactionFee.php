<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionFee extends Model
{
    protected $fillable = [
        'txid',
        'is_paid',
        'amount',
    ];
}
