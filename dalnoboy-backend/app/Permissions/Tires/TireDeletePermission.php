<?php

namespace App\Permissions\Tires;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class TireDeletePermission extends BasePermission
{
    public const KEY = TirePermissionsGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.' . TirePermissionsGroup::KEY . '.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
