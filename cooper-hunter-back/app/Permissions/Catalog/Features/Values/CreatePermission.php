<?php

namespace App\Permissions\Catalog\Features\Values;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.feature.value.create';

    public function getName(): string
    {
        return __('permissions.catalog.feature.value.grants.create');
    }

    public function getPosition(): int
    {
        return 51;
    }
}

