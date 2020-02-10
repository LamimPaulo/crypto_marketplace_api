<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasternodePlan extends Model
{
    protected $fillable = [
        'value',
        'months',
        'status',
    ];

    public function user_plans()
    {
        return $this->hasMany(MasternodeUserPlan::class, 'masternode_plan_id');
    }
}
