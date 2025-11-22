<?php


namespace App\Permissions\Vehicles;


use Core\Permissions\BasePermission;

class VehicleShowPermission extends BasePermission
{
    public const KEY = VehiclePermissionsGroup::KEY . '.show';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions.' . VehiclePermissionsGroup::KEY . '.grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
