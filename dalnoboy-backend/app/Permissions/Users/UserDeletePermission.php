<?php

namespace App\Permissions\Users;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class UserDeletePermission extends BasePermission
{
    public const KEY = UserPermissionsGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.' . UserPermissionsGroup::KEY . '.grants.delete');
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
