<?php


namespace App\Services\Permissions\Groups;


class PermissionGroupBilling extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'billing';
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
