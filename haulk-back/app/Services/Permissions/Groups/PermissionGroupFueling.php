<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupFueling extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'fueling';
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
