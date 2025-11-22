<?php


namespace App\Permissions\Vehicles\Schemas;


use Core\Permissions\BasePermission;

class VehicleSchemaShowPermission extends BasePermission
{
    public const KEY = VehicleSchemaPermissionsGroup::KEY . '.show';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions.' . VehicleSchemaPermissionsGroup::KEY . '.grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
