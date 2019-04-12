<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaypalAuth extends Model
{
    protected $fillable = ['app_id', 'access_token', 'expires_in'];
}
