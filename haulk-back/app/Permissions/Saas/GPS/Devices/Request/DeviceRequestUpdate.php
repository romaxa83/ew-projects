<?php

namespace App\Permissions\Saas\GPS\Devices\Request;

use App\Permissions\BasePermission;

class DeviceRequestUpdate extends BasePermission
{
    public const KEY = 'gps-device-request.update';

    public function getName(): string
    {
        return __('permissions.gps-device-request.grants.update');
    }

    public function getPosition(): int
    {
        return 31;
    }
}

