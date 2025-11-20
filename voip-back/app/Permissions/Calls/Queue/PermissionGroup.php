<?php

namespace App\Permissions\Calls\Queue;

use Core\Permissions\BasePermissionGroup;

class PermissionGroup extends BasePermissionGroup
{
    public const KEY = 'call.queue';

    public function getName(): string
    {
        return __('permissions.calls.queue.group');
    }
}
