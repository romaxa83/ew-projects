<?php


namespace App\Permissions\Drivers;


use Core\Permissions\BasePermission;

class DriverCreatePermission extends BasePermission
{
    public const KEY = DriverPermissionsGroup::KEY . '.create';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions. ' . DriverPermissionsGroup::KEY . ' .grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
