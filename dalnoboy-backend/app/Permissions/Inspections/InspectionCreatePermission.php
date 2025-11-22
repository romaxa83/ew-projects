<?php


namespace App\Permissions\Inspections;

use App\Enums\Permissions\UserRolesEnum;
use Core\Permissions\BasePermission;

class InspectionCreatePermission extends BasePermission
{
    public const KEY = InspectionPermissionsGroup::KEY . '.create';

    public static function forRole(string $role): bool
    {
        return $role === UserRolesEnum::INSPECTOR;
    }

    public function getName(): string
    {
        return __('permissions. ' . InspectionPermissionsGroup::KEY . ' .grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
