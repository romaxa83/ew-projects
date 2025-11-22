<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupTrailers extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'trailers';
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
