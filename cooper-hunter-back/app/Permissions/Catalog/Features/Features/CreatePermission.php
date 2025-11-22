<?php

namespace App\Permissions\Catalog\Features\Features;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.feature.feature.create';

    public function getName(): string
    {
        return __('permissions.catalog.feature.feature.grants.create');
    }

    public function getPosition(): int
    {
        return 61;
    }
}

