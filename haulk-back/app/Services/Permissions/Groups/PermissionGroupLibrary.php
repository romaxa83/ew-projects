<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupLibrary extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'library';
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
