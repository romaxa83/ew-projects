<?php

namespace App\Permissions\Saas\GPS\Devices\Request;

use App\Permissions\BasePermission;

class DeviceRequestList extends BasePermission
{
    public const KEY = 'gps-device-request.list';

    public function getName(): string
    {
        return __('permissions.gps-device-request.grants.list');
    }

    public function getPosition(): int
    {
        return 10;
    }
}

