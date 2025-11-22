<?php


namespace App\Permissions\Branches;


use Core\Permissions\BasePermission;

class BranchShowPermission extends BasePermission
{
    public const KEY = BranchPermissionsGroup::KEY . '.show';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions. ' . BranchPermissionsGroup::KEY . ' .grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
