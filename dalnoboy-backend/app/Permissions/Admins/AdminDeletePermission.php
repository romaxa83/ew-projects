<?php

namespace App\Permissions\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class AdminDeletePermission extends BasePermission
{

    public const KEY = 'admin.delete';

    public function getName(): string
    {
        return __('permissions.admin.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }

    public static function forRole(string $role): bool
    {
        return $role === AdminRolesEnum::SUPER_ADMIN;
    }
}
