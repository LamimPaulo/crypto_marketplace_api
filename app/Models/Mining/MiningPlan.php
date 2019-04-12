<?php

namespace App\Models\Mining;

use App\Enum\EnumMiningProfitType;
use App\Models\CoinQuote;
use App\Services\ConversorService;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed ths_quota_price
 * @property mixed profit_type
 */
class MiningPlan extends Model
{
    protected $fillable = [
        'name',
        'ths_total',
        'ths_quota',
        'ths_quota_price',
        'ths_quota_price_type',
        'profit',
        'profit_type',
        'profit_payout'
    ];

    protected $appends = ['thsQuotaPriceBtc','thsQuotaPriceUsd','thsQuotaPriceBrl', 'thsAquired', 'profitTypeName'];

    public function quotas()
    {
        return $this->hasMany(MiningQuota::class, 'mining_plan_id');
    }

    public function blocks()
    {
        return $this->hasMany(MiningQuota::class, 'mining_plan_id');
    }

    public function getThsQuotaPriceBtcAttribute()
    {
        $conversor = new ConversorService();
        $result = $conversor::BRL2BTCMIN($this->ths_quota_price);
        return (float)$result['amount'];
    }

    public function getThsQuotaPriceAttribute($value)
    {
        return (float)$value;
    }

    public function getThsQuotaPriceUsdAttribute()
    {
        $dollar = CoinQuote::where(['coin_id'=>3, 'quote_coin_id'=>2])->first()->average_quote;
        return (float)number_format($this->ths_quota_price / $dollar, 2, '.', '');
    }

    public function getThsQuotaPriceBrlAttribute()
    {
        return number_format($this->ths_quota_price, 2, ',', '.');
    }

    public function getThsAquiredAttribute()
    {
        return $this->quotas()->sum('ths_quota');
    }

    public function getProfitTypeNameAttribute()
    {
        return EnumMiningProfitType::TYPE[$this->profit_type];
    }
}
