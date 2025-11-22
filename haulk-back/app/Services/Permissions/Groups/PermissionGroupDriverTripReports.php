<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupDriverTripReports extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'driver-trip-reports';
    }

    public function getPermissions(): array
    {
        return [
            'read',
            'create',
            'update',
            'delete'
        ];
    }
}
