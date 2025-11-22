<?php

namespace App\Permissions\Catalog\Features\Features;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.feature.feature';

    public function getName(): string
    {
        return __('permissions.catalog.feature.feature.group');
    }

    public function getPosition(): int
    {
        return 60;
    }
}
