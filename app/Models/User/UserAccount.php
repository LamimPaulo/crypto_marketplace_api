<?php

namespace App\Models\User;

use App\Models\Bank;
use App\Models\Model;
use App\Models\PaymentProvider;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_id',
        'provider_id',
        'agency',
        'account',
        'agency_digit',
        'account_digit',
        'nickname',
        'type',
        'category',
        'observation',
        'email'
    ];

    protected $hidden = ['user_id'];

    public function provider()
    {
        return $this->belongsTo(PaymentProvider::class, 'provider_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function user_id()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
