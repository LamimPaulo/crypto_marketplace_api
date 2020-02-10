<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class MasternodeImport extends Model
{
    protected $fillable = [
        'months',
        'reward_address',
        'email',
        'is_sync',
        'is_rewarded',
    ];

    public function masternode()
    {
        return $this->belongsTo(Masternode::class, 'reward_address', 'reward_address');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }
}
