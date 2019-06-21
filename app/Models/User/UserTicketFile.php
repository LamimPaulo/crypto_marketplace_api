<?php

namespace App\Models\User;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserTicketFile extends Model
{
    protected $fillable = [
        'user_ticket_message_id',
        'file'
    ];

    public function getFileAttribute($value)
    {
        $url = '';
        if (Storage::disk('s3')->has($value)) {
            $url = Storage::disk('s3')->temporaryUrl($value, Carbon::now()->addMinutes(20));
        }
        return $url;
    }
}
