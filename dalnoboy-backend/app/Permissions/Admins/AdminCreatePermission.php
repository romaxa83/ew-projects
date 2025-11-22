<?php

namespace App\Permissions\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class AdminCreatePermission extends BasePermission
{
    public const KEY = 'admin.create';

    public function getName(): string
    {
        return __('permissions.admin.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }

    public static function forRole(string $role): bool
    {
        return $role === AdminRolesEnum::SUPER_ADMIN;
    }
}
