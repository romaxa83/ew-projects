<?php

namespace App\Permissions\Users;

use Core\Permissions\BasePermission;

class UserShowPermission extends BasePermission
{
    public const KEY = UserPermissionsGroup::KEY . '.show';

    public function getName(): string
    {
        return __('permissions.' . UserPermissionsGroup::KEY . '.grants.show');
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
