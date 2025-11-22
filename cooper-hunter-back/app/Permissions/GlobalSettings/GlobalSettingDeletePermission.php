<?php

namespace App\Permissions\GlobalSettings;

use Core\Permissions\BasePermission;

class GlobalSettingDeletePermission extends BasePermission
{
    public const KEY = 'globalSetting.delete';

    public function getName(): string
    {
        return __('permissions.global_setting.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
