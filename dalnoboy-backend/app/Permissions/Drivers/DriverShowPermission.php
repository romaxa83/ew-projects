<?php


namespace App\Permissions\Drivers;


use Core\Permissions\BasePermission;

class DriverShowPermission extends BasePermission
{
    public const KEY = DriverPermissionsGroup::KEY . '.show';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions. ' . DriverPermissionsGroup::KEY . ' .grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
