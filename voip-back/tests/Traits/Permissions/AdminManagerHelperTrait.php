<?php

declare(strict_types=1);

namespace Tests\Traits\Permissions;

use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminCreatePermission;
use App\Permissions\Admins\AdminDeletePermission;
use App\Permissions\Admins\AdminListPermission;
use App\Permissions\Admins\AdminUpdatePermission;

trait AdminManagerHelperTrait
{
    use RoleHelperHelperTrait;

    protected function loginAsAdminManager(): Admin
    {
        return $this->loginAsAdmin()->assignRole(
            $this->generateRole(
                'Admin manager',
                [
                    AdminListPermission::KEY,
                    AdminCreatePermission::KEY,
                    AdminUpdatePermission::KEY,
                    AdminDeletePermission::KEY,
                ],
                Admin::GUARD
            )
        );
    }

    protected function loginAsAdminWithPermissions(array $permissions = [], Admin $admin = null): Admin
    {
        return $this->loginAsAdmin($admin)->assignRole(
            $this->generateRole(
                'Admin role',
                count($permissions) ? $permissions : [
                    AdminListPermission::KEY,
                    AdminCreatePermission::KEY,
                    AdminUpdatePermission::KEY,
                    AdminDeletePermission::KEY,
                ],
                Admin::GUARD
            )
        );
    }
}
