<?php

namespace App\Permissions\Security;

use Core\Permissions\BasePermission;

class IpAccessListPermission extends BasePermission
{
    public const KEY = 'ip-access.list';

    public function getName(): string
    {
        return __('permissions.ip-access.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
