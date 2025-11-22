<?php

namespace App\Permissions\News;

use Core\Permissions\BasePermission;

class NewsListPermission extends BasePermission
{
    public const KEY = NewsPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.news.grants.list');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
