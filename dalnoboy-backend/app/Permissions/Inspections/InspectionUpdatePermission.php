<?php


namespace App\Permissions\Inspections;


use Core\Permissions\BasePermission;

class InspectionUpdatePermission extends BasePermission
{
    public const KEY = InspectionPermissionsGroup::KEY . '.update';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions. ' . InspectionPermissionsGroup::KEY . ' .grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
