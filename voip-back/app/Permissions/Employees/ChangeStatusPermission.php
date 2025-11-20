<?php

namespace App\Permissions\Employees;

use Core\Permissions\BasePermission;

class ChangeStatusPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.change-status';

    public function getName(): string
    {
        return __('permissions.employees.grants.change-status');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

