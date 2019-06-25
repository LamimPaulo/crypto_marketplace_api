<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model
{
    protected $fillable = [
        'user_id',
        'message_id',
        'status',
    ];

    protected $hidden = ['created_at','updated_at'];
}