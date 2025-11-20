<?php

namespace App\Permissions\Schedules;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.schedules.grants.list');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

