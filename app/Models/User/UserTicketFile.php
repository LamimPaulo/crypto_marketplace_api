<?php

namespace App\Models\User;

use App\Services\FileApiService;
use Illuminate\Database\Eloquent\Model;

class UserTicketFile extends Model
{
    protected $fillable = [
        'user_ticket_message_id',
        'file',
        'api_id',
        'type',
    ];

    protected $appends = [
        'api_file'
    ];


//    public function getFileAttribute($value)
//    {
//        $url = '';
//        if (Storage::disk('s3')->has($value)) {
//            $url = Storage::disk('s3')->temporaryUrl($value, Carbon::now()->addMinutes(20));
//        }
//        return $url;
//    }

    public function getApiFileAttribute()
    {
        if ($this->api_id) {
            $fileApi = FileApiService::getFile($this->api_id);
            return $fileApi['file'];
        }

        return null;
    }
}
