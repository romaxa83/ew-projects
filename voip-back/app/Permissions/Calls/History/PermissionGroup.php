<?php

namespace App\Permissions\Calls\History;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'call.history';

    public function getName(): string
    {
        return __('permissions.calls.history.group');
    }
}
