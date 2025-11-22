<?php

namespace App\Permissions\Catalog\Troubleshoots\Troubleshoot;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.troubleshoot.troubleshoot';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.troubleshoot.group');
    }

    public function getPosition(): int
    {
        return 60;
    }
}
