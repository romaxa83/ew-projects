<?php

namespace App\Permissions\Sips;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'sips';

    public function getName(): string
    {
        return __('permissions.sips.group');
    }
}
