<?php

namespace App\Permissions\Supports;

use Core\Permissions\BasePermissionGroup;

class SupportPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'support';

    public function getName(): string
    {
        return __('permissions.support.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
