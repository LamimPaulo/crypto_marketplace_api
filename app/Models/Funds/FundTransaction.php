<?php

namespace App\Models\Funds;

use App\Enum\EnumFundTransactionCategory;
use App\Enum\EnumTransactionsStatus;
use App\Enum\EnumTransactionType;
use App\Models\Coin;
use App\Models\Transaction;
use App\User;
use Illuminate\Database\Eloquent\Model;

class FundTransaction extends Model
{
    protected $table = 'fund_transactions';

    protected $fillable = [
        'user_id',
        'fund_id',
        'coin_id',
        'transaction_id',
        'value',
        'tax',
        'profit_percent',
        'type',
        'category',
        'status',
    ];

    protected $appends = ['typeName', 'categoryName', 'statusName', 'dateLocal', 'updatedLocal', 'valueLocal', 'taxLocal'];

    //Appends
    public function getValueLocalAttribute()
    {
        if ($this->coin_id == 2) {
            return number_format($this->value, 2, '.', ',');
        }
        return sprintf("%.5f", $this->value);
    }

    public function getTaxLocalAttribute()
    {
        if ($this->coin_id == 2) {
            return number_format($this->tax, 2, '.', ',');
        }
        return sprintf("%.5f", $this->tax);
    }

    public function getCategoryNameAttribute()
    {
        return EnumFundTransactionCategory::CATEGORY[$this->category];
    }

    public function getTypeNameAttribute()
    {
        return EnumTransactionType::TYPES[$this->type];
    }

    public function getStatusNameAttribute()
    {
        return EnumTransactionsStatus::STATUS[$this->status];
    }

    public function getStatusClassAttribute()
    {
        return EnumTransactionsStatus::STATUS_CLASS[$this->status];
    }

    public function getDateLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }

    //Relations
    public function fund()
    {
        return $this->belongsTo(Funds::class, 'fund_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_id');
    }
}
