<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupHistory extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'history';
    }

    public function getPermissions(): array
    {
        return [
            //'create',
            'read',
            //'update',
            'delete',
        ];
    }
}
