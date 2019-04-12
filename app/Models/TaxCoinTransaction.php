<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxCoinTransaction extends Model
{
    protected $fillable = [
        'tax_coin_id',
        'crypto',
        'operation_type',
        'operation_id'
    ];
}
