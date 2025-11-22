<?php

namespace App\Permissions\Catalog\Videos\Group;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.video.group.delete';

    public function getName(): string
    {
        return __('permissions.catalog.video.group.grants.delete');
    }

    public function getPosition(): int
    {
        return 63;
    }
}
