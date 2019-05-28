<?php

namespace App\Models\User;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserEmailChange extends Model
{
    protected $fillable = [
        'old_email',
        'new_email',
        'user_id',
        'creator_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
