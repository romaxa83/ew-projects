<?php

namespace App\Permissions\Tires;

use Core\Permissions\BasePermission;

class TireUpdatePermission extends BasePermission
{
    public const KEY = TirePermissionsGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.' . TirePermissionsGroup::KEY . '.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }

    public static function forRole(string $role): bool
    {
        return true;
    }
}
