<?php

namespace App\Permissions\Catalog\Features\Features;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.feature.feature.list';

    public function getName(): string
    {
        return __('permissions.catalog.feature.feature.grants.list');
    }

    public function getPosition(): int
    {
        return 64;
    }
}
