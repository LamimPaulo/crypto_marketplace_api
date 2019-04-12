<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Ramsey\Uuid\Uuid;

class Model extends BaseModel
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Boot the Model.
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($instance) {
            $uuid4 = Uuid::uuid4();
            $instance->id = $uuid4->toString();
        });
    }
}
