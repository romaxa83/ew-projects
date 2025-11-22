<?php

namespace App\Permissions\Alerts;

use Core\Permissions\BasePermissionGroup;

class AlertPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'alert';

    public function getName(): string
    {
        return __('permissions.alert.group');
    }

    public function getPosition(): int
    {
        return 11;
    }
}
