<?php

namespace App\Models;

use App\Enum\EnumMasternodeStatus;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MasternodeUserPlan extends Model
{
    protected $fillable = [
        'user_id',
        'masternode_plan_id',
        'masternode_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected $hidden = [
        'user_id',
    ];

    protected $dates = [
        'start_date',
        'end_date',
    ];

    protected $appends = [
        'startDateLocal',
        'endDateLocal',
        'statusName',
    ];

    public function getStatusNameAttribute()
    {
        return EnumMasternodeStatus::STATUS[$this->status];
    }

    public function getStartDateLocalAttribute()
    {
        return $this->start_date->format('d/m/Y');
    }

    public function getEndDateLocalAttribute()
    {
        return $this->end_date->format('d/m/Y');
    }

    public function plan()
    {
        return $this->belongsTo(MasternodePlan::class, 'masternode_plan_id');
    }

    public function masternode()
    {
        return $this->belongsTo(Masternode::class, 'masternode_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
