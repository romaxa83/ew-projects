<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupUsers extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'users';
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
