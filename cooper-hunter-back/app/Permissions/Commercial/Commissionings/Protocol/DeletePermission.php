<?php

namespace App\Permissions\Commercial\Commissionings\Protocol;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.commissioning.protocol.grants.delete');
    }

    public function getPosition(): int
    {
        return 5;
    }
}
