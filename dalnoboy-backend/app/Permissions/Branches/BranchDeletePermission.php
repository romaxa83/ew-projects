<?php


namespace App\Permissions\Branches;


use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class BranchDeletePermission extends BasePermission
{
    public const KEY = BranchPermissionsGroup::KEY . '.delete';

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }

    public function getName(): string
    {
        return __('permissions. ' . BranchPermissionsGroup::KEY . ' .grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
