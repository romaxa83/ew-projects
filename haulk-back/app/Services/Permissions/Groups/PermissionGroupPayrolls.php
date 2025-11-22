<?php


namespace App\Services\Permissions\Groups;


class PermissionGroupPayrolls extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'payrolls';
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
