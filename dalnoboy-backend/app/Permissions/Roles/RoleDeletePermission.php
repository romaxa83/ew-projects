<?php

namespace App\Permissions\Roles;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class RoleDeletePermission extends BasePermission
{

    public const KEY = 'role.delete';

    public function getName(): string
    {
        return __('permissions.role.grants.delete');
    }

    public function getPosition(): int
    {
        return 40;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
