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
            DB::beginTransaction();

            if (!Permission::first()) {
                $permission = [
                    [
                        'id' => 1,
                        'name' => 'dashboard_general',
                        'desc' => 'Dashboard Gerais'
                    ], [
                        'id' => 2,
                        'name' => 'dashboard_fiat',
                        'desc' => 'Dashboard Operações Fiat'
                    ], [
                        'id' => 3,
                        'name' => 'dashboard_crypto',
                        'desc' => 'Dashboard Operações Crypto'
                    ], [
                        'id' => 4,
                        'name' => 'user_mail_change',
                        'desc' => 'Trocar email do Usuário'
                    ], [
                        'id' => 5,
                        'name' => 'user_2fa_disable',
                        'desc' => 'Desabilitar 2FA do Usuário'
                    ], [
                        'id' => 6,
                        'name' => 'user_documents',
                        'desc' => 'Validação de Documentos do Usuário'
                    ], [
                        'id' => 7,
                        'name' => 'user_tickets',
                        'desc' => 'Tickets de Usuário'
                    ], [
                        'id' => 8,
                        'name' => 'fiat_deposits',
                        'desc' => 'Confirmação de Depósitos'
                    ], [
                        'id' => 9,
                        'name' => 'fiat_withdrawals',
                        'desc' => 'Processamento de Saques'
                    ], [
                        'id' => 10,
                        'name' => 'fiat_withdrawals_taxes',
                        'desc' => 'Taxas de Saque'
                    ], [
                        'id' => 11,
                        'name' => 'fiat_holidays',
                        'desc' => 'Feriados (Saques)'
                    ], [
                        'id' => 12,
                        'name' => 'crypto_above_limit',
                        'desc' => 'Processamento de Transações Crypto'
                    ], [
                        'id' => 13,
                        'name' => 'nanotech_withdrawals',
                        'desc' => 'Saques Nanotech'
                    ], [
                        'id' => 14,
                        'name' => 'nanotech_configs',
                        'desc' => 'Configurações Nanotech'
                    ], [
                        'id' => 15,
                        'name' => 'funds',
                        'desc' => 'Configurações de Fundos'
                    ], [
                        'id' => 16,
                        'name' => 'levels',
                        'desc' => 'Configurações de Níveis'
                    ], [
                        'id' => 17,
                        'name' => 'messages',
                        'desc' => 'Mensagens'
                    ], [
                        'id' => 18,
                        'name' => 'wallet_order',
                        'desc' => 'Ordem das Carteiras'
                    ], [
                        'id' => 19,
                        'name' => 'coins_config',
                        'desc' => 'Configurações das Moedas'
                    ], [
                        'id' => 20,
                        'name' => 'Contas Bancárias',
                        'desc' => 'Configurações de Contas do Sistema'
                    ], [
                        'id' => 21,
                        'name' => 'system',
                        'desc' => 'Configurações Gerais'
                    ], [
                        'id' => 22,
                        'name' => 'navi_report',
                        'desc' => 'Configurações de Fundos'
                    ], [
                        'id' => 23,
                        'name' => 'assign_permission',
                        'desc' => 'Configurações de Permissões'
                    ],
                ];

                $role = Role::create([
                    'name' => 'super_admin',
                    'desc' => 'Super Admin'
                ]);

                foreach ($permission as $p) {
                    Permission::create($p);

                    RolePermission::create([
                        'role_id' => $role->id,
                        'permission_id' => $p['id'],
                        'type' => EnumPermissionType::ACCESS
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
