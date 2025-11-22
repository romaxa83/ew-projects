<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupContacts extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'contacts';
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
