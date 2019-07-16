<?php

namespace App\Models\Mining;

use Illuminate\Database\Eloquent\Model;

class MiningWorker extends Model
{
    protected $fillable = [
        'mining_pool_id',
        'worker',
        'last_share',
        'score',
        'alive',
        'shares',
        'hashrate'
    ];

    public function pool()
    {
        return $this->belongsTo(MiningPool::class, 'mining_pool_id');
    }
}
