<?php

namespace App\Permissions\News;

use Core\Permissions\BasePermission;

class NewsCreatePermission extends BasePermission
{
    public const KEY = NewsPermissionGroup::KEY . '.create';

    public function getName(): string
    {
        return __('permissions.news.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
