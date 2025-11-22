<?php

namespace App\Services\Permissions\Groups\GPS;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupDevices extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'gps-devices';
    }

    public function getPermissions(): array
    {
        return [
            'read',
            'create',
            'update',
            'attach_to_vehicle',
            'list',
            'request',
        ];
    }
}
