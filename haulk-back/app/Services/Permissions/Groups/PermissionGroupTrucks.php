<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupTrucks extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'trucks';
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
