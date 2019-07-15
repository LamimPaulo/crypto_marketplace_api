<?php

namespace App\Models\User;

use App\Enum\EnumUserLevelLimitType;
use App\Models\Coin;
use Illuminate\Database\Eloquent\Model;

class UserLevelLimit extends Model
{
    protected $fillable = [
        'user_level_id',
        'coin_id',
        'type',
        'limit',
        'limit_auto',
    ];

    protected $appends = [
        'typeName'
    ];

    //Appends
    public function getTypeNameAttribute()
    {
        return EnumUserLevelLimitType::TYPES[$this->type];
    }

    //Relations
    public function coin()
    {
        return $this->belongsTo(Coin::class, 'coin_id');
    }

    public function level()
    {
        return $this->belongsTo(UserLevel::class, 'user_level_id');
    }

}
