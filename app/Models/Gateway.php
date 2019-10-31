<?php

namespace App\Models;

use App\Enum\EnumGatewayCategory;
use App\Enum\EnumGatewayStatus;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed created_at
 * @property mixed user_id
 * @property mixed coin_id
 * @property mixed status
 * @property mixed type
 * @property mixed address
 * @property mixed amount
 * @property mixed value
 * @property mixed tx
 * @property mixed tax
 * @property mixed txid
 * @property mixed received
 * @property mixed confirmations
 * @property mixed fiat_coin_id
 * @property mixed fiat_amount
 * @property mixed mining_user_id
 * @property mixed category
 */
class Gateway extends Model
{
    protected $table = 'gateway';

    protected $fillable = [
        'user_id',
        'gateway_api_key_id',
        'coin_id',
        'status',
        'type',
        'address',
        'amount',
        'value',
        'tx',
        'tax',
        'txid',
        'received',
        'confirmations',
        'fiat_coin_id',
        'fiat_amount',
        'is_internal_payment',
        'payer_user_id',
        'category',
        'time_limit',
        'info',
        'txid_reverse',
    ];

    protected $appends = [
        'fiatAmountLocal',
        'timeLimitLocal',
        'createdLocal',
        'statusName',
        'categoryName',
        'endTime',
        'startTime'
    ];

    protected $hidden = ['user_id', 'id', 'coin_id', 'fiat_coin_id', 'type'];

    public function histStatus()
    {
        return $this->hasMany(GatewayStatus::class, 'gateway_id');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function fiat_coin()
    {
        return $this->belongsTo(Coin::class, 'fiat_coin_id');
    }

    public static function listByType(int $type)
    {
        return self::where('type', '=', $type)->get();
    }

    public function getAmountAttribute($value)
    {
        return sprintf("%.8f", $value);
    }

    public function getReceivedAttribute($value)
    {
        return sprintf("%.8f", $value);
    }

    public function getFiatAmountLocalAttribute()
    {
        if ($this->fiat_coin_id == Coin::getByAbbr("BRL")->id) {
            return number_format($this->fiat_amount, 2, ',', '.');
        }

        if ($this->fiat_coin_id == Coin::getByAbbr("USD")->id) {
            return sprintf("%.2f", $this->fiat_amount);
        }
    }

    public function getTimeLimitLocalAttribute()
    {
        return Carbon::parse($this->time_limit)->format('d/m/Y H:i');
    }

    public function getEndTimeAttribute()
    {
        return Carbon::parse($this->time_limit)->format('M d, Y H:i:s');
    }

    public function getStartTimeAttribute()
    {
        return Carbon::now()->format('M d, Y H:i:s');
    }

    public function getStatusNameAttribute()
    {
        return EnumGatewayStatus::SITUATION[$this->status];
    }

    public function getCategoryNameAttribute()
    {
        return EnumGatewayCategory::CATEGORY[$this->category];
    }

    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public static function listByTypeAndStatus(int $type, int $status)
    {
        return self::where('type', '=', $type)->where('status', '=', $status)->get();
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'address', 'toAddress');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
