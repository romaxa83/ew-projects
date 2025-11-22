<?php

namespace App\Permissions\Catalog\Troubleshoots\Group;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.troubleshoot.group';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.group.group');
    }

    public function getPosition(): int
    {
        return 60;
    }
}
