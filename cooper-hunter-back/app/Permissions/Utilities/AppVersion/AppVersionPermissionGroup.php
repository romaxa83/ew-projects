<?php

namespace App\Permissions\Utilities\AppVersion;

use Core\Permissions\BasePermissionGroup;

class AppVersionPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'app_version';

    public function getName(): string
    {
        return __('permissions.app_version.group');
    }

    public function getPosition(): int
    {
        return 101;
    }
}