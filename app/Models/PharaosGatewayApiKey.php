<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PharaosGatewayApiKey extends Model
{
    protected $fillable = ['user_id', 'api_key', 'secret', 'ip', 'type'];

    protected $hidden = ['secret'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
