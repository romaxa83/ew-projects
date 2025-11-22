<?php

namespace App\Permissions\Catalog\Videos\Group;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.video.group';

    public function getName(): string
    {
        return __('permissions.catalog.video.group.group');
    }

    public function getPosition(): int
    {
        return 60;
    }
}
