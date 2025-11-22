<?php

namespace App\Permissions\Commercial\Commissionings\Protocol;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.commissioning.protocol.grants.list');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

