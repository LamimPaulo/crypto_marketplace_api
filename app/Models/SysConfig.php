<?php

namespace App\Models;

use App\Enum\EnumWeekdays;
use Illuminate\Database\Eloquent\Model;

class SysConfig extends Model
{
    protected $fillable = [
        'buy_tax',
        'sell_tax',
        'deposit_min_valor',
        'send_min_btc',
        'ip',
        'secret',
        'time_gateway',
        'min_withdrawal_hour',
        'max_withdrawal_hour',
        'withdrawal_days',
    ];

    protected $appends = ['withdrawalDaysStr','withdrawalDaysArr'];

    //Acessors
    public function getMinWithdrawalHourAttribute($value)
    {
        return substr($value, 0, -3);
    }

    public function getMaxWithdrawalHourAttribute($value)
    {
        return substr($value, 0, -3);
    }

    //Appends
    public function getWithdrawalDaysArrAttribute()
    {
        $days = explode(",", $this->withdrawal_days);
        $strdays = [];
        foreach ($days as $d) {
            array_push($strdays, EnumWeekdays::STR[$d]);
        }
        return $strdays;
    }

    public function getWithdrawalDaysStrAttribute()
    {
        $days = explode(",", $this->withdrawal_days);
        $strdays = '';
        foreach ($days as $d) {
            $strdays .= EnumWeekdays::STR[$d]. ", ";
        }
        return substr($strdays, 0, -2);
    }


}
