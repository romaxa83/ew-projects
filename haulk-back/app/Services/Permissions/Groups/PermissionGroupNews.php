<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupNews extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'news';
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
