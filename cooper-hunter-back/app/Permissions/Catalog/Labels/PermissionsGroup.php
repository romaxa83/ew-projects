<?php

namespace App\Permissions\Catalog\Labels;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.label';

    public function getName(): string
    {
        return __('permissions.catalog.label.group');
    }

    public function getPosition(): int
    {
        return 40;
    }
}
