<?php

namespace App\Permissions\Departments;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'departments';

    public function getName(): string
    {
        return __('permissions.departments.group');
    }
}
