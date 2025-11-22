<?php

namespace App\Permissions\GlobalSettings;

use Core\Permissions\BasePermission;

class GlobalSettingCreatePermission extends BasePermission
{
    public const KEY = 'globalSetting.create';

    public function getName(): string
    {
        return __('permissions.global_setting.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
