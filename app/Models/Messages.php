<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $fillable = [
      'user_id',
      'type',
      'subject',
      'content',
      'status'
    ];

    protected $hidden = ['created_at', 'updated_at'];
    protected $table = 'messages';

    public function user() {
        return $this->belongsTo(User::class);
    }
}
