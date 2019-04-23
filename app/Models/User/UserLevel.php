<?php

namespace App\Models\User;

use App\Models\Product;
use App\Models\TaxCoin;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed name
 * @property mixed product_id
 * @property mixed limit_btc_diary
 * @property mixed limit_brl_diary
 * @property mixed limit_transaction_auto
 * @property mixed brokerage_fee
 * @property mixed is_referrable
 * @property mixed referral_profit
 * @property mixed is_active
 * @property mixed is_allowed_sell_for_fiat
 * @property mixed is_allowed_buy_with_fiat
 * @property mixed nanotech_lqx_fee
 * @property mixed nanotech_btc_fee
 * @property mixed masternode_fee
 */
class UserLevel extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'limit_btc_diary',
        'limit_brl_diary',
        'limit_transaction_auto',
        'brokerage_fee',
        'is_referrable',
        'referral_profit',
        'is_active',
        'is_allowed_sell_for_fiat',
        'is_allowed_buy_with_fiat',
        'nanotech_lqx_fee',
        'nanotech_btc_fee',
        'masternode_fee'
    ];

    protected $hidden = [];

    protected $appends = [
        'btcDiary',
        'brlDiary',
        'transactionAuto',
        'nanotechLqxPercent',
        'nanotechBtcPercent',
        'masternodePercent',
    ];

    //Appends
    public function getBtcDiaryAttribute()
    {
        return sprintf('%.5f', $this->limit_btc_diary);
    }

    public function getBrlDiaryAttribute()
    {
        return 'R$ ' . number_format($this->limit_brl_diary, 2, ',', '.');
    }

    public function getTransactionAutoAttribute()
    {
        return sprintf('%.5f', $this->limit_transaction_auto);
    }

    public function getNanotechLqxPercentAttribute()
    {
        return sprintf('%.2f', $this->nanotech_lqx_fee);
    }

    public function getNanotechBtcPercentAttribute()
    {
        return sprintf('%.2f', $this->nanotech_btc_fee);
    }

    public function getMasternodePercentAttribute()
    {
        return sprintf('%.2f', $this->nanotech_masternode_fee);
    }

    //Relations
    public function users()
    {
        return $this->hasMany(User::class, 'user_level_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function taxes()
    {
        return $this->hasMany(TaxCoin::class, 'user_level_id');
    }

    public function tax_crypto()
    {
        return $this->hasMany(TaxCoin::class, 'user_level_id')->where('coin_id', 1);
    }

    public function tax_brl()
    {
        return $this->hasMany(TaxCoin::class, 'user_level_id')->where('coin_id', 2);
    }
}
