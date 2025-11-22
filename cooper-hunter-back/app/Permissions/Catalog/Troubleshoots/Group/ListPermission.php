<?php

namespace App\Permissions\Catalog\Troubleshoots\Group;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.troubleshoot.group.list';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.group.grants.list');
    }

    public function getPosition(): int
    {
        return 64;
    }
}
