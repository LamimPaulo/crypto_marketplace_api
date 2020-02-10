<?php

namespace App\Models;

use App\Enum\EnumMasternodeStatus;
use App\User;
use Illuminate\Database\Eloquent\Model;

class MasternodeHist extends Model
{
    protected $fillable = [
        'masternode_id',
        'user_id',
        'status',
        'info',
    ];

    protected $appends = [
        'statusName'
    ];

    public function getCategoryNameAttribute()
    {
        return EnumMasternodeStatus::STATUS[$this->status];
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
