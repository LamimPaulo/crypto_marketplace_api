<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed payment_coin
 */
class GatewayApiKey extends Model
{
    protected $fillable = ['user_id', 'api_key', 'secret', 'ip', 'payment_coin'];

    protected $hidden = ['secret'];

    protected $appends = ['wallet_abbr', 'ip_front'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getIpAttribute($value)
    {
        return strlen($value) > 1 ? $value : '';
    }

    public function getIpFrontAttribute($value)
    {
        return strlen($value) > 1 ? $value : 'Qualquer IP';
    }

    public function getWalletAbbrAttribute()
    {
        return Coin::find($this->payment_coin)->abbr;
    }
}
