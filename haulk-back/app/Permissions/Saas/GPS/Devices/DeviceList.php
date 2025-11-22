<?php

namespace App\Permissions\Saas\GPS\Devices;

use App\Permissions\BasePermission;

class DeviceList extends BasePermission
{
    public const KEY = 'gps-device.list';

    public function getName(): string
    {
        return __('permissions.gps-device.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}
