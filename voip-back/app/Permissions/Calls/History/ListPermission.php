<?php

namespace App\Permissions\Calls\History;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.calls.history.list');
    }

    public function getPosition(): int
    {
        return 3;
    }
}


