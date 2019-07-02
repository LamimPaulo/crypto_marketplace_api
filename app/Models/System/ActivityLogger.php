<?php

namespace App\Models\System;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed causer_type
 * @property string causer_id
 */
class ActivityLogger extends Model
{
    public static function boot()
    {
        parent::boot();

        static::creating(function (ActivityLogger $item) {
            $item->causer_id = auth()->user()->id;
            $item->causer_type = User::class;
        });
    }

    protected $fillable = [
        'log_name',
        'description',
        'subject_id',
        'subject_type',
        'causer_id',
        'causer_type',
        'properties'
    ];

    protected $dates = ['created_at'];

    protected $appends = ['localCreated'];

    protected $table = 'user_activity_log';

    public function getLocalCreatedAttribute()
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function causer() {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function user() {
        return $this->belongsTo(User::class, 'subject_id');
    }


}
