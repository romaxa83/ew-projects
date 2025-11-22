<?php

namespace App\Permissions\Catalog\Videos\Link;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.video.link.update';

    public function getName(): string
    {
        return __('permissions.catalog.video.link.grants.update');
    }

    public function getPosition(): int
    {
        return 42;
    }
}

