<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupCompanySettings extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'company-settings';
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
