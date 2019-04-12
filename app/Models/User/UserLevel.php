<?php

namespace App\Models\User;

use App\Models\Product;
use App\Models\TaxCoin;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed name
 * @property mixed limit_btc_diary
 * @property mixed limit_brl_diary
 * @property mixed limit_usd_diary
 * @property mixed limit_transaction_auto
 * @property mixed brokerage_fee
 * @property mixed is_referrable
 * @property mixed referral_profit
 * @property mixed gateway_tax
 */
class UserLevel extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'limit_btc_diary',
        'limit_brl_diary',
        'limit_usd_diary',
        'limit_transaction_auto',
        'brokerage_fee',
        'is_referrable',
        'referral_profit',
        'is_gateway_elegible',
        'gateway_tax',
        'is_gateway_mmn_elegible',
        'gateway_mmn_tax',
        'is_card_elegible',
        'is_active',
        'is_allowed_buy_with_fiat',
        'is_allowed_sell_by_fiat'
    ];

    protected $hidden = [];

    protected $appends = [
        'btcDiary',
        'brlDiary',
        'usdDiary',
        'transactionAuto',
        'brokeragePercent',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'user_level_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getBtcDiaryAttribute()
    {
        return sprintf('%.8f', $this->limit_btc_diary);
    }

    public function getBrlDiaryAttribute()
    {
        return 'R$ ' . number_format($this->limit_brl_diary, 2, ',', '.');
    }

    public function getUsdDiaryAttribute()
    {
        return '$ ' . sprintf('%.2f', $this->limit_usd_diary);
    }

    public function getTransactionAutoAttribute()
    {
        return sprintf('%.8f', $this->limit_transaction_auto);
    }

    public function getBrokeragePercentAttribute()
    {
        return sprintf('%.2f', $this->brokerage_fee);
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

    public function tax_usd()
    {
        return $this->hasMany(TaxCoin::class, 'user_level_id')->where('coin_id', 3);
    }
}
