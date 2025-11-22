<?php

namespace App\Permissions\Catalog\Videos\Group;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.video.group.create';

    public function getName(): string
    {
        return __('permissions.catalog.video.group.grants.create');
    }

    public function getPosition(): int
    {
        return 61;
    }
}

