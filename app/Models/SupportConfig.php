<?php

namespace App\Models;

use App\Enum\EnumWeekdays;
use Illuminate\Database\Eloquent\Model;

class SupportConfig extends Model
{
    protected $fillable = [
        'days_off',
        'fri_close_time',
        'mon_opening_time'        
    ];

    protected $appends = ['daysOffStr','daysOffArr'];

    //Acessors
    public function getFriCloseTimeAttribute($value)
    {
        return substr($value, 0, -3);
    }

    public function getMonOpeningTimeAttribute($value)
    {
        return substr($value, 0, -3);
    }

    //Appends
    public function getdaysOffArrAttribute()
    {
        $days = explode(",", $this->days_off);
        $strdays = [];
        foreach ($days as $d) {
            array_push($strdays, EnumWeekdays::STR[$d]);
        }
        return $strdays;
    }

    public function getdaysOffStrAttribute()
    {
        $days = explode(",", $this->days_off);
        $strdays = '';
        foreach ($days as $d) {
            $strdays .= EnumWeekdays::STR[$d]. ", ";
        }
        return substr($strdays, 0, -2);
    }


}
