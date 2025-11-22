<?php

namespace App\Permissions\Users;

use App\Permissions\BasePermissionGroup;

class UserPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'user';

    public function getName(): string
    {
        return __('permissions.user.group');
    }

    public function getPosition(): int
    {
        return 30;
    }
}
