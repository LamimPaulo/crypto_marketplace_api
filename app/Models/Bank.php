<?php

namespace App\Models;

use App\Models\System\SystemAccount;
use App\Models\User\UserAccount;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $fillable = ['code', 'name', 'main'];

    public function accounts()
    {
        return $this->hasMany(UserAccount::class, 'bank_id');
    }

    public function sys_accounts()
    {
        return $this->hasMany(SystemAccount::class, 'bank_id');
    }
}
