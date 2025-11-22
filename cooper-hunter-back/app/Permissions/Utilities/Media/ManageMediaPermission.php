<?php

namespace App\Permissions\Utilities\Media;

use Core\Permissions\BasePermission;

class ManageMediaPermission extends BasePermission
{
    public const KEY = 'manage_media.manage';

    public function getName(): string
    {
        return __('permissions.manage_media.grants.manage');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
