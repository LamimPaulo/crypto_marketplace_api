<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    protected $fillable = ['email', 'status', 'description'];
}
