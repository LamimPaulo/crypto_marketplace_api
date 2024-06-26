<?php

namespace App\Models;

use App\Enum\EnumMasternodeStatus;
use App\Models\User\UserWallet;
use App\User;
use Carbon\Carbon;
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
        'privkey',
        'status',
        'label',
    ];

    protected $hidden = [
        'coin_id',
        'user_id',
        'privkey',
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
            'privkey',
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
        return $this->created_at ? $this->created_at->format('d/m/Y H:i') : Carbon::now()->format('d/m/Y H:i');
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d/m/Y H:i') : Carbon::now()->format('d/m/Y H:i');
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
        return $this->belongsTo(UserWallet::class, 'fee_address', 'address');
    }

    public function plans()
    {
        return $this->hasMany(MasternodeUserPlan::class, 'masternode_id');
    }

    public function hist()
    {
        return $this->hasMany(MasternodeHist::class, 'masternode_id');
    }

    public function getBalanceAttribute()
    {
        return $this->wallet()->pluck('balance')[0] ?? 0;
    }

}
