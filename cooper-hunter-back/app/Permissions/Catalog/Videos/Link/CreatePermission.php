<?php

namespace App\Permissions\Catalog\Videos\Link;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.video.link.create';

    public function getName(): string
    {
        return __('permissions.catalog.video.link.grants.create');
    }

    public function getPosition(): int
    {
        return 51;
    }
}

