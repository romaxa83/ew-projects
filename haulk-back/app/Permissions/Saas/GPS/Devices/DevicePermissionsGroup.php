<?php

namespace App\Permissions\Saas\GPS\Devices;

use App\Permissions\BasePermissionGroup;

class DevicePermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'gps-device';

    public function getName(): string
    {
        return __('permissions.gps-device.group');
    }
}
