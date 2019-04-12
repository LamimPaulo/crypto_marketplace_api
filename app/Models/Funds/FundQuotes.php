<?php

namespace App\Models\Funds;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FundQuotes extends Model
{
    protected $fillable = [
        'user_id',
        'fund_id',
        'quote',
        'value',
        'amount'
    ];

    protected $appends = ['updatedLocal'];

    public function fund()
    {
        return $this->belongsTo(Funds::class, 'fund_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }

    public static function increments($transaction)
    {
        FundQuotes::where('user_id', '=', $transaction->user_id)
            ->where('fund_id', '=', $transaction->fund_id)
            ->increment('quote', (string)$transaction->quote);

        FundQuotes::where('user_id', '=', $transaction->user_id)
            ->where('fund_id', '=', $transaction->fund_id)
            ->increment('amount', (string)$transaction->amount);
    }

    public static function decrements($transaction)
    {
        FundQuotes::where('user_id', '=', $transaction->user_id)
            ->where('fund_id', '=', $transaction->fund_id)
            ->decrement('quote', (string)$transaction->quote);

        FundQuotes::where('user_id', '=', $transaction->user_id)
            ->where('fund_id', '=', $transaction->fund_id)
            ->decrement('amount', (string)$transaction->amount);
    }
}
