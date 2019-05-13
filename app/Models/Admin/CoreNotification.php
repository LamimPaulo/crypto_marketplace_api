<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class CoreNotification extends Model
{
    protected $fillable = ['email', 'status', 'description'];
}
