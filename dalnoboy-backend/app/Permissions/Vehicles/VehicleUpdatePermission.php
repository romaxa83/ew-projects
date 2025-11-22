<?php


namespace App\Permissions\Vehicles;


use Core\Permissions\BasePermission;

class VehicleUpdatePermission extends BasePermission
{
    public const KEY = VehiclePermissionsGroup::KEY . '.update';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions.' . VehiclePermissionsGroup::KEY . '.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
