<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupTags extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'tags';
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
