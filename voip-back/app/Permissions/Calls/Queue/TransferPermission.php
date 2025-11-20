<?php

namespace App\Permissions\Calls\Queue;

use Core\Permissions\BasePermission;

class TransferPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.transfer';

    public function getName(): string
    {
        return __('permissions.calls.queue.transfer');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
