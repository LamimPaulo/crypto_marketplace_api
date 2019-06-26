<?php

namespace App\Models\User;

use App\Enum\EnumUserTicketsDepartments;
use App\Enum\EnumUserTicketsStatus;
use App\User;
use Illuminate\Database\Eloquent\Model;

class UserTicket extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'department',
        'subject'
    ];

    protected $appends = [
        'departmentName',
        'statusName',
        'statusClass',
        'createdLocal',
        'updatedLocal'
    ];

    //Appends
    public function getStatusNameAttribute()
    {
        return EnumUserTicketsStatus::STATUS[$this->status];
    }

    public function getStatusClassAttribute()
    {
        return EnumUserTicketsStatus::STATUS_CLASS[$this->status];
    }

    public function getDepartmentNameAttribute()
    {
        return EnumUserTicketsDepartments::DEPARTMENT[$this->department];
    }

    public function getCreatedLocalAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    public function getUpdatedLocalAttribute()
    {
        return $this->updated_at->format('d/m/Y H:i');
    }

    //Relations
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(UserTicketMessage::class, 'user_ticket_id');
    }
}
