<?php

namespace App\Models;

use App\Models\Funds\Funds;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'transaction_id',
        'provider_id',
        'symbol',
        'order_id',
        'client_order_id',
        'price',
        'orig_qty',
        'executed_qty',
        'cummulative_quote_qty',
        'status',
        'time_in_force',
        'type',
        'side',
        'stop_price',
        'iceberg_qty',
        'time',
        'update_time',
        'is_working',
        'fund_id'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function provider()
    {
        return $this->belongsTo(CoinProvider::class, 'provider_id');
    }

    public function fund()
    {
        return $this->belongsTo(Funds::class, 'fund_id');
    }
}
