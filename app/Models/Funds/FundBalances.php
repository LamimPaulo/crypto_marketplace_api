<?php

namespace App\Models\Funds;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FundBalances extends Model
{
    protected $table = 'fund_balances';

    protected $fillable = [
        'user_id',
        'fund_id',
        'balance_blocked',
        'balance_free',
        'end_date'
    ];

    protected $appends = ['finalDateLocal','startDateLocal'];

    //Appends
    public function getFinalDateLocalAttribute()
    {
        return Carbon::parse($this->end_date)->format("d/m/Y");
    }

    public function getStartDateLocalAttribute()
    {
        return Carbon::parse($this->created_at)->format("d/m/Y");
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

    //Functions
    public static function increments_blocked($balance, $value)
    {
        FundBalances::where([
            'fund_id' => $balance->fund_id,
            'user_id' => $balance->user_id
        ])->increment('balance_blocked', (string)$value);
    }

    public static function increments_free($balance, $value)
    {
        FundBalances::where([
            'fund_id' => $balance->fund_id,
            'user_id' => $balance->user_id
        ])->increment('balance_free', (string)$value);
    }

    public static function decrements_blocked($balance, $value)
    {
        FundBalances::where([
            'fund_id' => $balance->fund_id,
            'user_id' => $balance->user_id
        ])->decrement('balance_blocked', (string)$value);
    }

    public static function decrements_free($balance, $value)
    {
        FundBalances::where([
            'fund_id' => $balance->fund_id,
            'user_id' => $balance->user_id
        ])->decrement('balance_free', (string)$value);
    }
}
