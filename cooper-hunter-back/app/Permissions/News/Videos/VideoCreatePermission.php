<?php

namespace App\Permissions\News\Videos;

use Core\Permissions\BasePermission;

class VideoCreatePermission extends BasePermission
{
    public const KEY = VideoPermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.videos.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
