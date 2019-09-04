<?php

namespace App\Models\Exchange;

use App\Models\Coin;
use App\Models\CoinQuote;
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

    protected $appends = [
        'dateLocal',
        'basePriceUSD',
        'quotePriceUSD',
        'totalUSD',
        'basePriceBRL',
        'quotePriceBRL',
        'totalBRL',
        'amountLocal',
        'profitLocal',
        'profitUSD',
        'profitBRL',
        'profitPercentLocal',
        ];

    private function _quote(){
        return CoinQuote::where(['coin_id' => Coin::getByAbbr("USD")->id, 'quote_coin_id' => Coin::getByAbbr("BRL")->id, ])->first()->average_quote ?? 0;
    }

    public function getDateLocalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y H:i');
    }

    public function getBasePriceUSDAttribute()
    {
        return "$ " . number_format($this->base_price, '2', '.', ',');
    }

    public function getQuotePriceUSDAttribute()
    {
        return "$ " . number_format($this->quote_price, '2', '.', ',');
    }

    public function getTotalUSDAttribute()
    {
        return "$ " . number_format($this->total, '2', '.', ',');
    }

    public function getProfitUSDAttribute()
    {
        return "$ " . number_format($this->profit * $this->base_price, '2', '.', ',');
    }

    public function getProfitBRLAttribute()
    {
        return "R$ " . number_format($this->profit * ($this->base_price * $this->_quote()), '2', ',', '.');
    }

    public function getBasePriceBRLAttribute()
    {
        return "R$ " . number_format($this->base_price * $this->_quote(), '2', ',', '.');
    }

    public function getQuotePriceBRLAttribute()
    {
        return "R$ " . number_format($this->quote_price * $this->_quote(), '2', ',', '.');
    }

    public function getTotalBRLAttribute()
    {
        return "R$ " . number_format($this->total * $this->_quote(), '2', ',', '.');
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
