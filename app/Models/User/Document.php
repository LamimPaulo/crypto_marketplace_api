<?php

namespace App\Models\User;

use App\Models\Model;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property integer document_type_id
 * @property string user_id
 * @property boolean status
 * @property string path
 * @property string ext
 */
class Document extends Model
{
    protected $fillable = ['document_type_id', 'user_id', 'status', 'path', 'ext'];

    protected $appends = ['file'];

    public function type()
    {
        return $this->belongsTo(DocumentType::class, 'document_type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFileAttribute()
    {
        $url = '';
        if (Storage::disk('s3')->has($this->path)) {
            $url = Storage::disk('s3')->temporaryUrl($this->path, Carbon::now()->addMinutes(10));
        }
        return $url;
    }
}
