<?php

namespace App\Permissions\News;

use Core\Permissions\BasePermissionGroup;

class NewsPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'news';

    public function getName(): string
    {
        return __('permissions.news.group');
    }

    public function getPosition(): int
    {
        return 1000;
    }
}
