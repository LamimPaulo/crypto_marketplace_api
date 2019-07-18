<?php

namespace App\Services;


use App\Permission;
use App\RolePermission;
use App\UserRole;

class PermissionService
{
    public static function list($user = null)
    {
        if (is_null($user)) {
            $user = auth()->user()->id;
        }

        $user_role = UserRole::with([
            'role' => function ($role) {
                return $role->with([
                    'permissions' => function ($permissions) {
                        return $permissions->with('permission');
                    }
                ]);
            }
        ])->where('user_id', $user)->first();

        $up = [];

        foreach ($user_role->role->permissions as $p) {
            $up[$p->permission->name] = $p->type;
        }

        return $up;
    }

    public static function permission($permission_name, $user = null)
    {
        if (is_null($user)) {
            $user = auth()->user()->id;
        }


        $user_role = UserRole::where('user_id', $user)->first();
        $permission = Permission::where('name', $permission_name)->first();

        $type = RolePermission::where([
            'role_id' => $user_role->role_id,
            'permission_id' => $permission->id
        ])->first();

        return $type ? $type->type : 0;
    }
}
