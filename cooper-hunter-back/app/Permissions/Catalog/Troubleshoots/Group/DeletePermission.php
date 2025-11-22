<?php

namespace App\Permissions\Catalog\Troubleshoots\Group;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.troubleshoot.group.delete';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.group.grants.delete');
    }

    public function getPosition(): int
    {
        return 63;
    }
}
