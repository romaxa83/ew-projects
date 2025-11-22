<?php

namespace App\Permissions\Catalog\Features\Specifications;

use Core\Permissions\BasePermissionGroup;

class PermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'catalog.feature.specification';

    public function getName(): string
    {
        return __('permissions.catalog.feature.specifications.group');
    }

    public function getPosition(): int
    {
        return 60;
    }
}
