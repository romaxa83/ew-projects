<?php

namespace App\Permissions\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class AdminUpdatePermission extends BasePermission
{

    public const KEY = 'admin.update';

    public function getName(): string
    {
        return __('permissions.admin.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }

    public static function forRole(string $role): bool
    {
        return $role === AdminRolesEnum::SUPER_ADMIN;
    }
}
