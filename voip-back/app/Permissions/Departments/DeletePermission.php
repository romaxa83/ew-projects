<?php

namespace App\Permissions\Departments;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.departments.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

