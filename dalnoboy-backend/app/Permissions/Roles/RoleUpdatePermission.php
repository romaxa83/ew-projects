<?php

namespace App\Permissions\Roles;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class RoleUpdatePermission extends BasePermission
{

    public const KEY = 'role.update';

    public function getName(): string
    {
        return __('permissions.role.grants.update');
    }

    public function getPosition(): int
    {
        return 30;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
