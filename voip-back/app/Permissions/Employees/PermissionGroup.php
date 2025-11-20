<?php

namespace App\Permissions\Employees;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'employees';

    public function getName(): string
    {
        return __('permissions.employees.group');
    }
}
