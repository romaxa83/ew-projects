<?php

namespace App\Permissions\Settings;

use Core\Permissions\BasePermissionGroup;

class SettingsPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'settings';

    public function getName(): string
    {
        return __('permissions.settings.group');
    }

    public function getPosition(): int
    {
        return 0;
    }
}
