<?php

namespace App\Models;

use App\Enum\EnumUserLevelLimitType;
use App\Models\Nanotech\Nanotech;
use App\Models\Nanotech\NanotechType;
use App\Models\User\UserLevelLimit;
use App\Models\User\UserWallet;
use Illuminate\Database\Eloquent\Model;

class Coin extends Model
{
    protected $fillable = [
        'name',
        'shortname',
        'abbr',
        'is_active',
        'is_wallet',
        'is_masternode',
        'decimal',
        'is_crypto',
        'fee_low',
        'fee_avg',
        'fee_high',
        'buy_tax',
        'sell_tax',
        'tx_explorer',
        'icon',
        'wallet_order',
        'core_limit_balance',
        'core_limit_percent',
        'withdrawal_address',
        'core_balance',
        'core_status'
    ];

    protected $hidden = ['id', 'created_at', 'updated_at'];

    public function quote()
    {
        return $this->hasMany(CoinQuote::class);
    }

    public function quote_brl()
    {
        return $this->hasOne(CoinQuote::class)->where(['quote_coin_id' => Coin::getByAbbr("BRL")->id]);
    }

    public function limits()
    {
        return $this->hasMany(UserLevelLimit::class, 'coin_id');
    }

    public function internal_limit()
    {
        return $this->hasOne(UserLevelLimit::class, 'coin_id')->where('type', EnumUserLevelLimitType::INTERNAL);
    }

    public function external_limit()
    {
        return $this->hasOne(UserLevelLimit::class, 'coin_id')->where('type', EnumUserLevelLimitType::EXTERNAL);
    }

    public static function getByAbbr($abbr)
    {
        return self::where('abbr', '=', $abbr)->first();
    }

    public static function listAllActive()
    {
        return self::where('is_active', '=', 1)
            ->select('id', 'name', 'shortname', 'abbr')
            ->get();
    }

    public function investments()
    {
        return $this->hasMany(Nanotech::class, 'coin_id');
    }

    public function wallets()
    {
        return $this->hasMany(UserWallet::class, 'coin_id');
    }

    public function masternodes()
    {
        return $this->hasMany(Masternode::class, 'coin_id');
    }

    public function nanotech_types()
    {
        return $this->hasMany(NanotechType::class, 'coin_id');
    }
}
