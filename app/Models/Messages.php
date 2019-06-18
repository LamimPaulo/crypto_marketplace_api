<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $fillable = [
      'user_id',
      'user_email',
      'type',
      'subject',
      'content',
      'status'
    ];
//    protected $hidden = ['id', 'created_at', 'updated_at'];
    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'messages';
}
