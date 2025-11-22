<?php

namespace App\Services\Permissions\Groups\BodyShop;

use App\Services\Permissions\Groups\PermissionGroupAbstract;

class PermissionGroupSettings extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'settings-bs';
    }

    public function getPermissions(): array
    {
        return [
            'read',
            'update',
        ];
    }
}
