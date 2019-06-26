<?php

namespace App\Services;


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

    public static function permission($name, $user = null)
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
        ])
            ->whereHas('role', function ($role) use ($name) {
                return $role->whereHas('permissions', function ($permissions) use ($name) {
                    return $permissions->whereHas('permission', function ($permission) use ($name) {
                        return $permission->where('name', 'LIKE', $name);
                    });
                });
            })
            ->where('user_id', $user)->first();

        return $user_role ? $user_role->role->permissions[0]->type : 0 ;
    }
}
