<?php


namespace App\Permissions\Inspections;


use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class InspectionDeletePermission extends BasePermission
{
    public const KEY = InspectionPermissionsGroup::KEY . '.delete';

    public static function forRole(string $role): bool
    {
        return $role === AdminRolesEnum::SUPER_ADMIN;
    }

    public function getName(): string
    {
        return __('permissions. ' . InspectionPermissionsGroup::KEY . ' .grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
