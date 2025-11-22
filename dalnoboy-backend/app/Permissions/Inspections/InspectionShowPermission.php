<?php


namespace App\Permissions\Inspections;


use Core\Permissions\BasePermission;

class InspectionShowPermission extends BasePermission
{
    public const KEY = InspectionPermissionsGroup::KEY . '.show';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions. ' . InspectionPermissionsGroup::KEY . ' .grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
