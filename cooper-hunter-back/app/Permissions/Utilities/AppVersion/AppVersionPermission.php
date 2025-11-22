<?php

namespace App\Permissions\Utilities\AppVersion;

use Core\Permissions\BasePermission;

class AppVersionPermission extends BasePermission
{
    public const KEY = 'app_version.manage';

    public function getName(): string
    {
        return __('permissions.app_version.grants.manage');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
