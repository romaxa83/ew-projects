<?php


namespace App\Permissions\Drivers;


use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class DriverDeletePermission extends BasePermission
{
    public const KEY = DriverPermissionsGroup::KEY . '.delete';

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }

    public function getName(): string
    {
        return __('permissions. ' . DriverPermissionsGroup::KEY . ' .grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
