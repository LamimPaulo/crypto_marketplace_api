<?php

namespace App\Models;

use App\Models\System\SystemAccount;
use App\Models\User\UserAccount;
use Illuminate\Database\Eloquent\Model;

class PaymentProvider extends Model
{
    protected $fillable = ['name', 'email'];

    public function accounts()
    {
        return $this->hasMany(UserAccount::class, 'provider_id');
    }

    public function sys_accounts()
    {
        return $this->hasMany(SystemAccount::class, 'provider_id');
    }
}
