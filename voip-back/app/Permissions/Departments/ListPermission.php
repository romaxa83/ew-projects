<?php

namespace App\Permissions\Departments;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.departments.grants.list');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

