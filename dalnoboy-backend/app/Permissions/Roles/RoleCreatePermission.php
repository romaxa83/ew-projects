<?php

namespace App\Permissions\Roles;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class RoleCreatePermission extends BasePermission
{
    public const KEY = 'role.create';

    public function getName(): string
    {
        return __('permissions.role.grants.create');
    }

    public function getPosition(): int
    {
        return 20;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
