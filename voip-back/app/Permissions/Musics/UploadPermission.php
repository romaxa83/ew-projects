<?php

namespace App\Permissions\Musics;

use Core\Permissions\BasePermission;

class UploadPermission extends BasePermission
{
    public const KEY = PermissionGroup::KEY . '.upload';

    public function getName(): string
    {
        return __('permissions.musics.grants.upload');
    }

    public function getPosition(): int
    {
        return 3;
    }
}

