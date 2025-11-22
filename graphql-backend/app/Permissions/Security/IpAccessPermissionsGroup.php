<?php

namespace App\Permissions\Security;

use Core\Permissions\BasePermissionGroup;

class IpAccessPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'ip-access';

    public function getName(): string
    {
        return __('permissions.ip-access.group');
    }

    public function getPosition(): int
    {
        return 120;
    }
}
