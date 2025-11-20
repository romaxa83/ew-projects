<?php

namespace App\Permissions\Schedules;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'schedules';

    public function getName(): string
    {
        return __('permissions.schedules.group');
    }
}
