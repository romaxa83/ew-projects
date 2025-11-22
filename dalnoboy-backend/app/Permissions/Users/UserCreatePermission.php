<?php

namespace App\Permissions\Users;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class UserCreatePermission extends BasePermission
{
    public const KEY = UserPermissionsGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.' . UserPermissionsGroup::KEY . '.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
