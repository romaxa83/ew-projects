<?php

namespace App\Permissions\Clients;

use Core\Permissions\BasePermission;

class ClientUpdatePermission extends BasePermission
{
    public const KEY = ClientPermissionsGroup::KEY . '.update';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions.' . ClientPermissionsGroup::KEY . '.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
