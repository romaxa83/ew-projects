<?php

namespace App\Permissions\Clients;

use Core\Permissions\BasePermission;

class ClientCreatePermission extends BasePermission
{
    public const KEY = ClientPermissionsGroup::KEY . '.create';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions.' . ClientPermissionsGroup::KEY . '.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
