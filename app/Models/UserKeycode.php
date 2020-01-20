<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserKeycode extends Model
{
    protected $fillable = [
        'user_id',
        'api_key'
    ];
}
