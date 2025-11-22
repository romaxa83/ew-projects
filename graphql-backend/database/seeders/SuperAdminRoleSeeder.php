<?php

namespace Database\Seeders;

use App\Models\Admins\Admin;
use Throwable;

class SuperAdminRoleSeeder extends BaseDefaultRoleSeeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        make_transaction(
            function () {
                $role = $this->getPermissionService()->firstOrCreateSuperAdminRole();

                $this->checkRolePermissions($role);
            }
        );
    }

    protected function getGuardName(): string
    {
        return Admin::GUARD;
    }

    protected function getRoleName(): string
    {
        return config('permission.roles.super_admin');
    }
}
