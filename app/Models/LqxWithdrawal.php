<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class LqxWithdrawal extends Model
{
    protected $fillable = [
        'date',
        'is_executed',
        'percent'
    ];

    protected $appends = [
        'dateLocal'
    ];

    public function getDateLocalAttribute()
    {
        return Carbon::parse($this->date)->format('d/m/Y');
    }

}
