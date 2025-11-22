<?php

namespace App\Permissions\GlobalSettings;

use Core\Permissions\BasePermission;

class GlobalSettingUpdatePermission extends BasePermission
{
    public const KEY = 'globalSetting.update';

    public function getName(): string
    {
        return __('permissions.global_setting.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
