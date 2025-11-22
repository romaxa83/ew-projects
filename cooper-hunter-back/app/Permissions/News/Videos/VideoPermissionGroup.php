<?php

namespace App\Permissions\News\Videos;

use Core\Permissions\BasePermissionGroup;

class VideoPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'videos';

    public function getName(): string
    {
        return __('permissions.videos.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
