<?php

namespace App\Models\Mining;

use App\User;
use Illuminate\Database\Eloquent\Model;

class MiningQuotaProfit extends Model
{
    protected $fillable = [
        'user_id',
        'block',
        'ths_quota',
        'reward',
        'is_paid',
        'date_found'
    ];

    protected $dates = ['date_found'];

    protected $appends = ['foundLocal', 'updatedLocal'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function block_info()
    {
        return $this->hasOne(MiningBlock::class, 'block', 'block');
    }

    public function getFoundLocalAttribute()
    {
        return $this->date_found->format('d/m/Y H:i');
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }
}
