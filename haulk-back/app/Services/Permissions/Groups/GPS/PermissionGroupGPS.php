<?php

namespace App\Services\Permissions\Groups\GPS;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupGPS extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'gps';
    }

    public function getPermissions(): array
    {
        return [
            'read',
            'alerts',
            'devices',
            'subscription',
        ];
    }
}
