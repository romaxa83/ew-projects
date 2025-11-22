<?php

namespace App\Permissions\Managers;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class ManagerUpdatePermission extends BasePermission
{
    public const KEY = ManagerPermissionsGroup::KEY . '.update';

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }

    public function getName(): string
    {
        return __('permissions.' . ManagerPermissionsGroup::KEY . '.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
