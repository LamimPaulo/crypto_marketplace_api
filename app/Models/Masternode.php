<?php

namespace App\Models;

use App\Enum\EnumMasternodeStatus;
use App\Models\User\UserWallet;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Masternode extends Model
{
    use SoftDeletes;

    //
    protected $fillable = [
        'coin_id',
        'user_id',
        'ip',
        'reward_address',
        'payment_address',
        'fee_address',
        'status',
    ];

    protected $appends = [
        'createdLocal',
        'updatedLocal',
        'deletedLocal',
        'statusName',
        'statusColor',
        'balance',
    ];

    public static function hiddenAttr()
    {
        return [
            'id',
            'statusColor',
            'createdLocal',
            'updatedLocal',
            'deletedLocal',
            'created_at',
            'updated_at',
            'deleted_at',
            'user_id',
            'coin_id',
        ];
    }

    public function getStatusNameAttribute()
    {
        return EnumMasternodeStatus::STATUS[$this->status];
    }

    public function getStatusColorAttribute()
    {
        return EnumMasternodeStatus::COLOR[$this->status];
    }

    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }

    public function getDeletedLocalAttribute()
    {
        if ($this->deleted_at) {
            return $this->deleted_at->format('d/m/Y H:i');
        }
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wallet()
    {
        return $this->belongsTo(UserWallet::class, 'reward_address','address');
    }

    public function getBalanceAttribute()
    {
        return $this->wallet()->pluck('balance')[0] ?? 0;
    }

}
