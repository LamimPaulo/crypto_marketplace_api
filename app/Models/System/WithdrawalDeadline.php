<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;

class WithdrawalDeadline extends Model
{
    protected $fillable = ['deadline', 'tax', 'status'];
}
