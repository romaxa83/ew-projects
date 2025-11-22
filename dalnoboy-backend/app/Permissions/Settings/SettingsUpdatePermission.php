<?php

namespace App\Permissions\Settings;

use App\Enums\Permissions\AdminRolesEnum;
use Core\Permissions\BasePermission;

class SettingsUpdatePermission extends BasePermission
{
    public const KEY = SettingsPermissionsGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.' . SettingsPermissionsGroup::KEY . '.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }

    public static function forRole(string $role): bool
    {
        return in_array($role, AdminRolesEnum::getValues());
    }
}
