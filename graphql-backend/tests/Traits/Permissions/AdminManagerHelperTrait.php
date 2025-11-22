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

    protected function loginAsAdminManager(): void
    {
        $this->loginAsAdmin()->assignRole(
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
}
