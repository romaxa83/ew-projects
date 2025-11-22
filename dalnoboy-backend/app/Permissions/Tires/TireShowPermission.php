<?php

namespace App\Permissions\Tires;

use Core\Permissions\BasePermission;

class TireShowPermission extends BasePermission
{
    public const KEY = TirePermissionsGroup::KEY . '.show';

    public function getName(): string
    {
        return __('permissions.' . TirePermissionsGroup::KEY . '.grants.show');
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
