<?php

namespace App\Permissions\Admins;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class AdminShowPermission extends BasePermission
{
    public const KEY = AdminPermissionsGroup::KEY . '.show';

    public function getName(): string
    {
        return __('permissions.' . AdminPermissionsGroup::KEY . '.grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }

    public static function forRole(string $role): bool
    {
        return $role === AdminRolesEnum::SUPER_ADMIN;
    }
}
