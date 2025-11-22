<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupFuelCard extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'fuel-cards';
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
