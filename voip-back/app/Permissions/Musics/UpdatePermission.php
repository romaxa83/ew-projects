<?php

namespace App\Permissions\Musics;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.musics.grants.update');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
