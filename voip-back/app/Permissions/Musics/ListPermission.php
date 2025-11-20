<?php

namespace App\Permissions\Musics;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.musics.grants.list');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
