<?php

namespace App\Permissions\Saas\GPS\Devices;

use App\Permissions\BasePermission;

class DeviceUpdate extends BasePermission
{
    public const KEY = 'gps-device.update';

    public function getName(): string
    {
        return __('permissions.gps-device.grants.update');
    }

    public function getPosition(): int
    {
        return 31;
    }
}
