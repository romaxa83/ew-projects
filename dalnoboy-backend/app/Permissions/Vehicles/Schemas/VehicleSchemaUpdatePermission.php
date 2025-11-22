<?php


namespace App\Permissions\Vehicles\Schemas;


use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class VehicleSchemaUpdatePermission extends BasePermission
{
    public const KEY = VehicleSchemaPermissionsGroup::KEY . '.update';

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }

    public function getName(): string
    {
        return __('permissions.' . VehicleSchemaPermissionsGroup::KEY . '.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
