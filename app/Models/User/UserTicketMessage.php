<?php

namespace App\Models\User;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserTicketMessage extends Model
{
    protected $fillable = [
        'user_ticket_id',
        'user_id',
        'message'
    ];

    protected $appends = [
        'createdLocal',
        'updatedLocal'
    ];

    //Appends
    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }

    //Relations
    public function ticket()
    {
        return $this->belongsTo(UserTicket::class, 'user_ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function files()
    {
        return $this->hasMany(UserTicketFile::class, 'user_ticket_message_id');
    }
}
