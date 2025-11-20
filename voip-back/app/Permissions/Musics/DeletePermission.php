<?php

namespace App\Permissions\Musics;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.musics.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
