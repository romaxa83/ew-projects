<?php


namespace App\Permissions\Vehicles;


use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class VehicleDeletePermission extends BasePermission
{
    public const KEY = VehiclePermissionsGroup::KEY . '.delete';

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }

    public function getName(): string
    {
        return __('permissions.' . VehiclePermissionsGroup::KEY . '.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
