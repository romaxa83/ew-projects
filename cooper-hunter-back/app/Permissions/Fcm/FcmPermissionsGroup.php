<?php

namespace App\Permissions\Fcm;

use Core\Permissions\BasePermissionGroup;

class FcmPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'fcm';

    public function getName(): string
    {
        return __('permissions.fcm.group');
    }

    public function getPosition(): int
    {
        return 70;
    }
}
