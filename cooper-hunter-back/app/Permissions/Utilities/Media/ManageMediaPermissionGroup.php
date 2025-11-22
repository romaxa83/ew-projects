<?php

namespace App\Permissions\Utilities\Media;

use Core\Permissions\BasePermissionGroup;

class ManageMediaPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'manage_media';

    public function getName(): string
    {
        return __('permissions.manage_media.group');
    }

    public function getPosition(): int
    {
        return 100;
    }
}
