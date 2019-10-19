<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasternodeInfo extends Model
{
    protected $fillable = [
        'nodes',
        'rewards',
    ];
}
