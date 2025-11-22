<?php

namespace App\Permissions\Catalog\Features\Values;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.feature.value';

    public function getName(): string
    {
        return __('permissions.catalog.feature.value.group');
    }

    public function getPosition(): int
    {
        return 50;
    }
}
