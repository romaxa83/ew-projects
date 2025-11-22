<?php

namespace App\Permissions\News;

use Core\Permissions\BasePermission;

class NewsDeletePermission extends BasePermission
{
    public const KEY = NewsPermissionGroup::KEY . '.delete';

    public function getName(): string
    {
        return __('permissions.news.grants.delete');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
