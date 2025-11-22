<?php

namespace App\Permissions\Catalog\Videos\Link;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.video.link.delete';

    public function getName(): string
    {
        return __('permissions.catalog.video.link.grants.delete');
    }

    public function getPosition(): int
    {
        return 53;
    }
}
