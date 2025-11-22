<?php

namespace App\Permissions\Settings;

use Core\Permissions\BasePermission;

class SettingsShowPermission extends BasePermission
{
    public const KEY = SettingsPermissionsGroup::KEY . '.show';

    public function getName(): string
    {
        return __('permissions.' . SettingsPermissionsGroup::KEY . '.grants.show');
    }

    public function getPosition(): int
    {
        return 1;
    }

    public static function forRole(string $role): bool
    {
        return true;
    }
}
