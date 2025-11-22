<?php


namespace App\Permissions\Drivers;


use Core\Permissions\BasePermission;

class DriverUpdatePermission extends BasePermission
{
    public const KEY = DriverPermissionsGroup::KEY . '.update';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions. ' . DriverPermissionsGroup::KEY . ' .grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
