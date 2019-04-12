<?php

namespace App\Models\Mining;

use App\Models\Mining\MiningPlan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed date_found
 */
class MiningBlock extends Model
{
    protected $fillable = [
        'mining_pool_id',
        'block',
        'is_mature',
        'date_found',
        'date_started',
        'hash',
        'confirmations',
        'total_shares',
        'total_score',
        'reward',
        'mining_duration',
        'nmc_reward'
    ];

    protected $appends = ['diff'];

    public function pool()
    {
        return $this->belongsTo(MiningPool::class, 'mining_pool_id');
    }

    public function getDiffAttribute()
    {
        return Carbon::parse($this->date_found)->diffForHumans();
    }
}
