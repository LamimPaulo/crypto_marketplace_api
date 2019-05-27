<?php

namespace App\Models\Nanotech;

use App\Enum\EnumNanotechOperationStatus;
use App\Enum\EnumNanotechOperationType;
use App\User;
use Illuminate\Database\Eloquent\Model;

class NanotechOperation extends Model
{
    protected $fillable = [
        'user_id',
        'investment_id',
        'amount',
        'brokerage_fee',
        'brokerage_fee_percentage',
        'profit_percent',
        'type',
        'status',
        'created_at'
    ];

    protected $appends = ['createdLocal', 'updatedLocal', 'amountLocal', 'statusName', 'typeName'];

    //Appends
    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format("d/m/Y H:i");
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format("d/m/Y H:i");
    }

    public function getAmountLocalAttribute()
    {
        return abs($this->amount);
    }

    public function getStatusNameAttribute()
    {
        return EnumNanotechOperationStatus::STATUS[$this->status];
    }

    public function getTypeNameAttribute()
    {
        return EnumNanotechOperationType::TYPES[$this->type];
    }

    //Relations
    public function investment()
    {
        return $this->belongsTo(Nanotech::class, 'investment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
