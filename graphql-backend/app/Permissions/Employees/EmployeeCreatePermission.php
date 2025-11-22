<?php

namespace App\Permissions\Employees;

use Core\Permissions\BasePermission;

class EmployeeCreatePermission extends BasePermission
{
    public const KEY = 'employee.create';

    public function getName(): string
    {
        return __('permissions.employee.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
