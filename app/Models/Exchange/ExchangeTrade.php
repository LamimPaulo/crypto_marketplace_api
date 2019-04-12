<?php

namespace App\Models\Exchange;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed created_at
 * @property mixed base_price
 * @property mixed quote_price
 * @property mixed amount
 * @property mixed profit_percent
 * @property mixed profit
 * @property mixed total
 */
class ExchangeTrade extends Model
{
    protected $fillable = [
        'symbol',
        'type',
        'side',
        'amount',
        'total',
        'price',
        'status',
        'fee',
        'profit',
        'profit_percent',
        'base_exchange',
        'base_price',
        'quote_exchange',
        'quote_price',
        'client_order_id'
    ];

    protected $appends = ['dateLocal', 'basePriceLocal', 'quotePriceLocal', 'amountLocal', 'profitLocal', 'profitPercentLocal', 'totalLocal'];

    public function getDateLocalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y H:i');
    }

    public function getBasePriceLocalAttribute()
    {
        return number_format($this->base_price, '2', ',', '.');
    }

    public function getQuotePriceLocalAttribute()
    {
        return number_format($this->quote_price, '2', ',', '.');
    }

    public function getTotalLocalAttribute()
    {
        return number_format($this->total, '2', ',', '.');
    }

    public function getAmountLocalAttribute()
    {
        return sprintf('%.8f', $this->amount);
    }

    public function getProfitLocalAttribute()
    {
        return sprintf('%.8f', $this->profit);
    }

    public function getProfitPercentLocalAttribute()
    {
        return sprintf('%.3f', $this->profit_percent);
    }
}
