<?php

namespace App\Permissions\News\Videos;

use Core\Permissions\BasePermission;

class VideoUpdatePermission extends BasePermission
{
    public const KEY = VideoPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.videos.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
