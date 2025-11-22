<?php

namespace App\Permissions\News\Videos;

use Core\Permissions\BasePermission;

class VideoDeletePermission extends BasePermission
{
    public const KEY = VideoPermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.videos.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
