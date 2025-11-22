<?php

namespace App\Services\Permissions\Groups\BodyShop;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupVehicleOwners extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'vehicle-owners';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'update',
            'delete',
            'add-comment',
            'delete-comment',
        ];
    }
}
