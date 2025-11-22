<?php

namespace App\Permissions\Saas\GPS\Alerts;

use App\Permissions\BasePermissionGroup;

class AlertPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'gps-alert';

    public function getName(): string
    {
        return __('permissions.gps-alert.group');
    }
}
