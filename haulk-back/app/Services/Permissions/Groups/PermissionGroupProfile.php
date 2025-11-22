<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupProfile extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'profile';
    }

    public function getPermissions(): array
    {
        return [
            //'create',
            'read',
            'update',
            'change-email',
            'cancel-request-email',
            //'delete',
        ];
    }
}
