<?php

namespace App\Permissions\Clients;

use Core\Permissions\BasePermission;

class ClientShowPermission extends BasePermission
{
    public const KEY = ClientPermissionsGroup::KEY . '.show';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions.' . ClientPermissionsGroup::KEY . '.grants.show');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
