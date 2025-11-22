<?php

namespace App\Permissions\Employees;

use Core\Permissions\BasePermission;

class EmployeeUpdatePermission extends BasePermission
{
    public const KEY = 'employee.update';

    public function getName(): string
    {
        return __('permissions.employee.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
