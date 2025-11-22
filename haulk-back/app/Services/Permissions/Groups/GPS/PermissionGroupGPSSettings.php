<?php

namespace App\Services\Permissions\Groups\GPS;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupGPSSettings extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'gps-settings';
    }

    public function getPermissions(): array
    {
        return [
            'read',
            'update-speed-limit',
        ];
    }
}
