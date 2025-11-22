<?php

namespace App\Permissions\Catalog\Troubleshoots\Group;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.troubleshoot.group.create';

    public function getName(): string
    {
        return __('permissions.catalog.troubleshoot.group.grants.create');
    }

    public function getPosition(): int
    {
        return 61;
    }
}
