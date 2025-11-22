<?php


namespace App\Permissions\Vehicles\Schemas;


use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class VehicleSchemaDeletePermission extends BasePermission
{
    public const KEY = VehicleSchemaPermissionsGroup::KEY . '.delete';

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }

    public function getName(): string
    {
        return __('permissions.' . VehicleSchemaPermissionsGroup::KEY . '.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
