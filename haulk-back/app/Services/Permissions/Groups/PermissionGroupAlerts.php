<?php


namespace App\Services\Permissions\Groups;


class PermissionGroupAlerts extends PermissionGroupAbstract
{

    public function getName(): string
    {
        return 'alerts';
    }

    public function getPermissions(): array
    {
        return [
            'read',
            'delete'
        ];
    }
}
