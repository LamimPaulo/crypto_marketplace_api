<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDashboard extends Model
{
    protected $fillable = [
        'id',
        'general_json',
        'dev_json'
    ];
}
