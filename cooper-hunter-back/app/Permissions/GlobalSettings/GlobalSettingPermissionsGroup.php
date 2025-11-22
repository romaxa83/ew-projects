<?php

namespace App\Permissions\GlobalSettings;

use Core\Permissions\BasePermissionGroup;

class GlobalSettingPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'globalSetting';

    public function getName(): string
    {
        return __('permissions.global_setting.group');
    }

    public function getPosition(): int
    {
        return 67;
    }
}
