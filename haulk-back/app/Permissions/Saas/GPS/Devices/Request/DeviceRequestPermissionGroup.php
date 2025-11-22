<?php

namespace App\Permissions\Saas\GPS\Devices\Request;

use App\Permissions\BasePermissionGroup;

class DeviceRequestPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'gps-device-request';

    public function getName(): string
    {
        return __('permissions.gps-device-request.group');
    }
}

