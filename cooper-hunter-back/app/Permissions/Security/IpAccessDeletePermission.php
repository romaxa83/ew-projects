<?php

namespace App\Permissions\Security;

use Core\Permissions\BasePermission;

class IpAccessDeletePermission extends BasePermission
{
    public const KEY = 'ip-access.delete';

    public function getName(): string
    {
        return __('permissions.ip-access.grants.delete');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
