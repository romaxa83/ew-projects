<?php

namespace App\Permissions\Calls\Queue;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.calls.queue.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
