<?php

namespace App\Services\Permissions\Groups\GPS;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionsGroupGpsMenu extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'gps-menu';
    }

    public function getPermissions(): array
    {
        return [
            'map-visible',
            'map-active',
            'device-visible',
            'device-active',
        ];
    }
}
