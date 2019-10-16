<?php

namespace App\Models\User;

use App\Models\Coin;
use App\Models\Model;
use App\Models\Transaction;
use App\User;

class UserWallet extends Model
{
    protected $fillable = [
        'balance',
        'address',
        'user_id',
        'coin_id',
        'type',
        'conversion_priority',
        'sync'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $appends = [
        'balance_rounded'
    ];

    public static function showByAddress($address)
    {
        return self::where('address', $address)->first();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }

    public function getBalanceRoundedAttribute()
    {
        return sprintf("%.{$this->coin->decimal}f", $this->balance);
    }
}
