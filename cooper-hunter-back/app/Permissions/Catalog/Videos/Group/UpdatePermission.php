<?php

namespace App\Permissions\Catalog\Videos\Group;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.video.group.update';

    public function getName(): string
    {
        return __('permissions.catalog.video.group.grants.update');
    }

    public function getPosition(): int
    {
        return 62;
    }
}

