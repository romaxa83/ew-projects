<?php

namespace App\Permissions\Schedules;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.schedules.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
