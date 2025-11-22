<?php

namespace App\Permissions\Employees;

use Core\Permissions\BasePermissionGroup;

class EmployeePermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'employee';

    public function getName(): string
    {
        return __('permissions.employee.group');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
