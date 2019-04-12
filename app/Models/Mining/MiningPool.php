<?php

namespace App\Models\Mining;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed round_started
 */
class MiningPool extends Model
{
    protected $fillable = [
        'unconfirmed_reward',
        'rating',
        'nmc_send_threshold',
        'unconfirmed_nmc_reward',
        'estimated_reward',
        'hashrate',
        'confirmed_nmc_reward',
        'send_threshold',
        'confirmed_reward',
        'active_workers',
        'round_started',
        'luck_30',
        'shares_cdf',
        'luck_b50',
        'luck_b10',
        'active_stratum',
        'ghashes_ps',
        'shares',
        'round_duration',
        'score',
        'luck_b250',
        'luck_7',
        'luck_1'
    ];

    protected $appends = ['roundStartedLocal'];

    public function blocks()
    {
        return $this->hasMany(MiningBlock::class, 'mining_pool_id');
    }

    public function getRoundStartedLocalAttribute()
    {
        return Carbon::parse($this->round_started)->subHours(2)->format('d/m/Y H:i');
    }

    public function getSharesCdfAttribute($value)
    {
        return round($value);
    }
}
