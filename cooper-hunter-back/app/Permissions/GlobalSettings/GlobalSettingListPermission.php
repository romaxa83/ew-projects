<?php

namespace App\Permissions\GlobalSettings;

use Core\Permissions\BasePermission;

class GlobalSettingListPermission extends BasePermission
{
    public const KEY = 'globalSetting.list';

    public function getName(): string
    {
        return __('permissions.global_setting.grants.list');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
