<?php

namespace App\Permissions\Security;

use Core\Permissions\BasePermission;

class IpAccessCreatePermission extends BasePermission
{
    public const KEY = 'ip-access.create';

    public function getName(): string
    {
        return __('permissions.ip-access.grants.create');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
