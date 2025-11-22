<?php

namespace App\Permissions\Catalog\Videos\Link;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.video.link.list';

    public function getName(): string
    {
        return __('permissions.catalog.video.link.grants.list');
    }

    public function getPosition(): int
    {
        return 54;
    }
}
