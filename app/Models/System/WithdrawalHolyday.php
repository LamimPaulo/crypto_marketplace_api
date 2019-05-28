<?php

namespace App\Models\System;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class WithdrawalHolyday extends Model
{
    protected $fillable = [
        'day',
        'info'
    ];

    protected $appends = [
        'createdLocal',
        'dayLocal',
    ];

    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getDayLocalAttribute()
    {
        return Carbon::parse($this->day)->format('d/m/Y');
    }

    public function getDayAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
}
