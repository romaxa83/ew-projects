<?php


namespace App\Permissions\Locations;


use Core\Permissions\BasePermission;

class RegionShowPermission extends BasePermission
{
    public const KEY = RegionPermissionGroup::KEY . '.show';

    public static function forRole(string $role): bool
    {
        return true;
    }

    public function getName(): string
    {
        return __('permissions. ' . RegionPermissionGroup::KEY . ' .grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
