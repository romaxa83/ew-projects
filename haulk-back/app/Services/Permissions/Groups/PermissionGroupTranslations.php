<?php

namespace App\Services\Permissions\Groups;

class PermissionGroupTranslations extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'translations';
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
