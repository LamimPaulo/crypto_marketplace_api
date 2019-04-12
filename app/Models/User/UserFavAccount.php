<?php

namespace App\Models\User;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserFavAccount extends Model
{
    protected $fillable = ['user_id', 'fav_user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fav_user()
    {
        return $this->belongsTo(User::class, 'fav_user_id');
    }
}
