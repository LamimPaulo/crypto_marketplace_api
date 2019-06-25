<?php

namespace App;

use App\Enum\EnumPermissionType;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $fillable = [
        'role_id',
        'permission_id',
        'type'
    ];

    protected $appends = [
        'typeName',
        'typeClass',
    ];

    public function getTypeNameAttribute()
    {
        return EnumPermissionType::TYPE[$this->type];
    }

    public function getTypeClassAttribute()
    {
        return EnumPermissionType::COLOR[$this->type];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
