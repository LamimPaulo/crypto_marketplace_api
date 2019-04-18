<?php

namespace App\Models\Nanotech;

use Illuminate\Database\Eloquent\Model;

class NanotechProfitPercent extends Model
{
    protected $fillable = [
        'percent',
        'day',
        'type_id'
    ];

    public function investment_type()
    {
        return $this->belongsTo(NanotechType::class, 'type_id');
    }
}
