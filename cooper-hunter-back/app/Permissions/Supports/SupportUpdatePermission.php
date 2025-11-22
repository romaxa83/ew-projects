<?php

namespace App\Permissions\Supports;

use Core\Permissions\BasePermission;

class SupportUpdatePermission extends BasePermission
{
    public const KEY = SupportPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.support.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
