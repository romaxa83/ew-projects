<?php

namespace App\Permissions\Catalog\Videos\Link;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.video.link';

    public function getName(): string
    {
        return __('permissions.catalog.video.link.group');
    }

    public function getPosition(): int
    {
        return 50;
    }
}
