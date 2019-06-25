<?php

namespace App\Http\Controllers\Admin;

use App\Enum\EnumPermissionType;
use App\Permission;
use App\Role;
use App\RolePermission;
use App\User;
use App\UserRole;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PermissionsController extends Controller
{
    public function roles()
    {
        try {
            return response([
                'roles' => $this->list_roles(),
                'permissions' => $this->list_permissions(),
                'users' => $this->list_users_without_role()
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function enum_permissions()
    {
        return EnumPermissionType::TYPE;
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'desc' => 'required'
        ]);

        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => Str::slug($request->desc),
                'desc' => $request->desc
            ]);

            foreach ($request->users as $user) {
                $user = User::where('email', $user['email'])->first();

                if (!$user->user_role()->exists()) {
                    UserRole::create([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                    ]);
                }
            }

            foreach ($request->permissions as $p) {
                RolePermission::create([
                    'role_id' => $role->id,
                    'permission_id' => $p['id'],
                    'type' => $p['type']
                ]);
            }

            DB::commit();

            return response([
                'status' => 'success',
                'roles' => $this->list_roles(),
                'permissions' => $this->list_permissions(),
                'users' => $this->list_users_without_role(),
                'message' => 'Criado com Sucesso!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updatePermissions(Request $request)
    {

        $request->validate([
            'desc' => 'required'
        ]);
        
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($request->id);
            $role->name = Str::slug($request->desc);
            $role->desc = $request->desc;
            $role->save();

            //if ($role->id> 1) {
                foreach ($request->permissions as $p) {
                    $permission = RolePermission::find($p['id']);
                    $permission->type = $p['type'];
                    $permission->save();
                }
            //}

            foreach ($request->new_users as $new_user) {
                $user = User::where('email', $new_user['email'])->first();

                if (!$user->user_role()->exists()) {
                    UserRole::create([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                    ]);
                }
            }

            DB::commit();

            return response([
                'status' => 'success',
                'roles' => $this->list_roles(),
                'permissions' => $this->list_permissions(),
                'users' => $this->list_users_without_role(),
                'message' => 'Atualizado com Sucesso!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function deleteUserRole($role, $user_email)
    {
        try {
            DB::beginTransaction();

            $user = User::where('email', $user_email)->first();

            if ($user->is_dev) {
                throw new \Exception("Você não pode remover um desenvolvedor do perfil solicitado.");
            }

            $user_role = UserRole::where([
                'user_id' => $user->id,
                'role_id' => $role,
            ]);

            $user_role->delete();

            DB::commit();

            return response([
                'status' => 'success',
                'roles' => $this->list_roles(),
                'permissions' => $this->list_permissions(),
                'users' => $this->list_users_without_role(),
                'message' => 'Usuário Removido com Sucesso!'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    private function list_roles()
    {
        return Role::with([
            'permissions' => function ($permissions) {
                return $permissions->with('permission');
            },
            'user_roles' => function ($user_roles) {
                return $user_roles->with('user');
            },
        ])->get();
    }

    private function list_permissions()
    {
        $perm = Permission::all();
        $permissions = [];
        foreach ($perm as $p) {
            $permissions[] = [
                'type' => EnumPermissionType::DENIED,
                'desc' => $p->desc,
                'id' => $p->id,
            ];
        }

        return $permissions;
    }

    private function list_users_without_role()
    {
        return User::where('is_admin', true)->doesnthave('user_role')->get();
    }
}
