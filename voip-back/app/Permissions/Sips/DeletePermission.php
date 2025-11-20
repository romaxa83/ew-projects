<?php

namespace App\Permissions\Sips;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.sips.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

