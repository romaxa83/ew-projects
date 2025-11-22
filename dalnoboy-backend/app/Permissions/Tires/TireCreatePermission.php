<?php

namespace App\Permissions\Tires;

use Core\Permissions\BasePermission;

class TireCreatePermission extends BasePermission
{
    public const KEY = TirePermissionsGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.' . TirePermissionsGroup::KEY . '.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }

    public static function forRole(string $role): bool
    {
        return true;
    }
}
