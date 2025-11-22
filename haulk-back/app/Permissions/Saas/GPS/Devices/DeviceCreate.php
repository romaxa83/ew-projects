<?php

namespace App\Permissions\Saas\GPS\Devices;

use App\Permissions\BasePermission;

class DeviceCreate extends BasePermission
{
    public const KEY = 'gps-device.create';

    public function getName(): string
    {
        return __('permissions.gps-device.grants.create');
    }

    public function getPosition(): int
    {
        return 30;
    }
}
