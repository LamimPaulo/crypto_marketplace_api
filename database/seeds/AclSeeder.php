<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Permission;
use App\Role;
use App\UserRole;
use App\RolePermission;
use App\Enum\EnumPermissionType;
use Illuminate\Support\Facades\DB;

class AclSeeder extends Seeder
{
    public function run()
    {
        try {
            UserRole::truncate();
            RolePermission::truncate();
            Role::truncate();
            Permission::truncate();

            DB::beginTransaction();

            if (!Permission::first()) {
                $permission = [
                    [
                        'name' => 'user_list',
                        'desc' => 'Listagem do Usuário'
                    ],[
                        'name' => 'user_mail_change',
                        'desc' => 'Trocar email do Usuário'
                    ], [
                        'name' => 'user_2fa_disable',
                        'desc' => 'Desabilitar 2FA do Usuário'
                    ], [
                        'name' => 'user_documents',
                        'desc' => 'Validação de Documentos do Usuário'
                    ], [
                        'name' => 'user_tickets',
                        'desc' => 'Tickets de Usuário'
                    ], [
                        'name' => 'fiat_menu',
                        'desc' => 'Menu Operações Fiat'
                    ],[
                        'name' => 'fiat_deposits',
                        'desc' => 'Confirmação de Depósitos'
                    ], [
                        'name' => 'fiat_withdrawals',
                        'desc' => 'Processamento de Saques'
                    ], [
                        'name' => 'fiat_withdrawals_taxes',
                        'desc' => 'Taxas de Saque'
                    ], [
                        'name' => 'fiat_holidays',
                        'desc' => 'Feriados (Saques)'
                    ], [
                        'name' => 'crypto_above_limit',
                        'desc' => 'Processamento de Transações Crypto'
                    ], [
                        'name' => 'nanotech_menu',
                        'desc' => 'Menu Nanotech'
                    ],[
                        'name' => 'nanotech_withdrawals',
                        'desc' => 'Saques Nanotech'
                    ], [
                        'name' => 'nanotech_configs',
                        'desc' => 'Configurações Nanotech'
                    ], [
                        'name' => 'funds',
                        'desc' => 'Configurações de Fundos'
                    ], [
                        'name' => 'levels',
                        'desc' => 'Configurações de Níveis'
                    ], [
                        'name' => 'messages',
                        'desc' => 'Mensagens'
                    ], [
                        'name' => 'config_menu',
                        'desc' => 'Menu Configurações'
                    ], [
                        'name' => 'wallet_order',
                        'desc' => 'Ordem das Carteiras'
                    ], [
                        'name' => 'coins_config',
                        'desc' => 'Configurações das Moedas'
                    ], [
                        'name' => 'system_accounts',
                        'desc' => 'Configurações de Contas do Sistema'
                    ], [
                        'name' => 'system',
                        'desc' => 'Configurações Gerais'
                    ], [
                        'name' => 'navi_report',
                        'desc' => 'Configurações de Fundos'
                    ], [
                        'name' => 'assign_permission',
                        'desc' => 'Configurações de Permissões'
                    ], [
                        'name' => 'gateway',
                        'desc' => 'Utilização do Gateway de Pagamentos'
                    ],
                ];

                $role = Role::create([
                    'name' => 'super_admin',
                    'desc' => 'Super Admin'
                ]);

                foreach ($permission as $p) {
                    $perm = Permission::create($p);

                    RolePermission::create([
                        'role_id' => $role->id,
                        'permission_id' => $perm->id,
                        'type' => EnumPermissionType::TOTAL
                    ]);
                }

                $admins = User::where('is_admin', true)->get();
                foreach ($admins as $admin) {
                    UserRole::create([
                        'user_id' => $admin->id,
                        'role_id' => $role->id
                    ]);
                }
            }
            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
