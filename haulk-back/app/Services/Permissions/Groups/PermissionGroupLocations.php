<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupLocations extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'locations';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'update',
            'delete',
        ];
    }
}
