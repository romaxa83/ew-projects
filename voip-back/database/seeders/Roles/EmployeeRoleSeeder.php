<?php

namespace Database\Seeders\Roles;

use Throwable;

class EmployeeRoleSeeder extends BaseRoleSeeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        makeTransaction(
            function () {
                $role = $this->getPermissionService()->firstOrCreateEmployee();

                if (!$role->wasRecentlyCreated) {
                    $this->getPermissionService()->seedAllPermissionsForGuard($role->guard_name);
                    $this->getPermissionService()->syncAllPermissionForRoleAndGuard($role);
                }
            }
        );
    }
}

