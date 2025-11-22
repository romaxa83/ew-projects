<?php

namespace App\Permissions\News;

use Core\Permissions\BasePermission;

class NewsUpdatePermission extends BasePermission
{
    public const KEY = NewsPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.news.grants.update');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
