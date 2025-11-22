<?php

namespace App\Permissions\Employees;

use Core\Permissions\BasePermission;

class EmployeeDeletePermission extends BasePermission
{
    public const KEY = 'employee.delete';

    public function getName(): string
    {
        return __('permissions.employee.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
