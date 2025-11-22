<?php

namespace App\Permissions\Catalog\Videos\Group;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.video.group.list';

    public function getName(): string
    {
        return __('permissions.catalog.video.group.grants.list');
    }

    public function getPosition(): int
    {
        return 64;
    }
}
