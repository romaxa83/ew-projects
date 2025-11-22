<?php

namespace App\Permissions\Catalog\Troubleshoots\Group;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.troubleshoot.group.update';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.group.grants.update');
    }

    public function getPosition(): int
    {
        return 62;
    }
}

