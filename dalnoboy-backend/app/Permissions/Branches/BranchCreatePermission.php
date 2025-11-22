<?php


namespace App\Permissions\Branches;


use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class BranchCreatePermission extends BasePermission
{
    public const KEY = BranchPermissionsGroup::KEY . '.create';

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }

    public function getName(): string
    {
        return __('permissions. ' . BranchPermissionsGroup::KEY . ' .grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
