<?php

namespace App\Permissions\News\Videos;

use Core\Permissions\BasePermission;

class VideoListPermission extends BasePermission
{
    public const KEY = VideoPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.videos.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
