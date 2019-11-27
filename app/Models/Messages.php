<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'subject',
        'content',
        'status',
        'created_at'
    ];

    protected $hidden = ['updated_at'];
    protected $table = 'messages';

    protected $appends = [
        'createdLocal'
    ];

    public function getCreatedLocalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d/m/Y H:i');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statuses()
    {
        return $this->hasMany(MessageStatus::class, 'message_id');
    }
}
