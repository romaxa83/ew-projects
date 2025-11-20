<?php

namespace App\Permissions\Calls\Queue;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.calls.queue.list');
    }

    public function getPosition(): int
    {
        return 3;
    }
}



