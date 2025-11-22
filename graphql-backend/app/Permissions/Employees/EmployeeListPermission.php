<?php

namespace App\Permissions\Employees;

use Core\Permissions\BasePermission;

class EmployeeListPermission extends BasePermission
{
    public const KEY = 'employee.list';

    public function getName(): string
    {
        return __('permissions.employee.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
