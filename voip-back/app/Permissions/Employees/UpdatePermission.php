<?php

namespace App\Permissions\Employees;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.employees.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
