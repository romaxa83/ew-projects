<?php

namespace App\Permissions\Security;

use Core\Permissions\BasePermission;

class IpAccessUpdatePermission extends BasePermission
{
    public const KEY = 'ip-access.update';

    public function getName(): string
    {
        return __('permissions.ip-access.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
