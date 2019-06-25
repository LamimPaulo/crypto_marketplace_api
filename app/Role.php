<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'desc'
    ];

    protected $appends = [
        'new_users'
    ];

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id');
    }

    public function user_roles()
    {
        return $this->hasMany(UserRole::class, 'role_id');
    }

    public function getNewUsersAttribute()
    {
        return [];
    }
}
