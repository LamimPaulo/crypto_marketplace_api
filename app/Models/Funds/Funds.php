<?php

namespace App\Models\Funds;

use App\Enum\EnumFundType;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed type
 * @property mixed buy_tax
 * @property mixed sell_tax
 * @property mixed admin_tax
 * @property mixed value
 * @property mixed start_price
 */
class Funds extends Model
{
    protected $fillable = [
        'name',
        'type',
        'buy_tax',
        'sell_tax',
        'admin_tax',
        'value',
        'start_price',
        'is_active',
        'start_amount'
    ];

    protected $appends = ['typeName', 'buyTaxRounded', 'sellTaxRounded', 'adminTaxRounded', 'valueLocal', 'startPriceLocal', 'startAmountLocal', 'percentChange'];

    public function getBuyTaxRoundedAttribute()
    {
        return sprintf("%.2f", $this->buy_tax);
    }

    public function getSellTaxRoundedAttribute()
    {
        return sprintf("%.2f", $this->sell_tax);
    }

    public function getAdminTaxRoundedAttribute()
    {
        return sprintf("%.2f", $this->admin_tax);
    }

    public function getStartAmountLocalAttribute()
    {
        return number_format($this->start_amount, 2, '.', ',');
    }

    public function getPercentChangeAttribute()
    {
        $diff = $this->value - $this->start_price;
        $change = $diff * 100 / $this->value;
        return sprintf("%.3f", $change);
    }

    public function getValueLocalAttribute()
    {
        return number_format($this->value, 2, ',', '.');
    }

    public function getStartPriceLocalAttribute()
    {
        return number_format($this->start_price, 2, ',', '.');
    }

    public function getTypeNameAttribute()
    {
        return EnumFundType::TYPE[$this->type];
    }

    public function coins()
    {
        return $this->hasMany(FundCoins::class, 'fund_id');
    }

    public function coins_percent()
    {
        return $this->hasMany(FundCoins::class, 'fund_id')->percent;
    }

    public function quotes()
    {
        return $this->hasMany(FundQuotes::class, 'fund_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'fund_id');
    }

}
