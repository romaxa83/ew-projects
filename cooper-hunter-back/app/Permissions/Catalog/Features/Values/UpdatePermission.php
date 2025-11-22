<?php

namespace App\Permissions\Catalog\Features\Values;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.feature.value.update';

    public function getName(): string
    {
        return __('permissions.catalog.feature.value.grants.update');
    }

    public function getPosition(): int
    {
        return 42;
    }
}

