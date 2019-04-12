<?php

namespace App\Helpers;

use App\User;

class ActivityLogger
{
    public static function log(
        $message,
        $subject_id = null,
        $subject_type = User::class,
        $properties = null,
        $log_name = 'hist',
        $causer_type = User::class
    )
    {
        \App\Models\System\ActivityLogger::create([
            'log_name' => $log_name,
            'description' => $message,
            'subject_id' => $subject_id,
            'subject_type' => $subject_type,
            'causer_id' => auth()->user()->id ?? null,
            'causer_type' => $causer_type,
            'properties' => json_encode($properties)
        ]);
    }
}
