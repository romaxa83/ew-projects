<?php


namespace App\Permissions\Vehicles;


use Core\Permissions\BasePermission;

class VehicleCreatePermission extends BasePermission
{
    public const KEY = VehiclePermissionsGroup::KEY . '.create';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions.' . VehiclePermissionsGroup::KEY . '.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
