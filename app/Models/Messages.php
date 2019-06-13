<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $fillable = [
      'user_id',
      'type',
      'subject',
      'message',
      'status'
    ];
    protected $hidden = ['id', 'created_at', 'updated_at'];
    protected $table = 'messages';
}
