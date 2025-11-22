<?php

namespace App\Permissions\Catalog\Features\Features;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.feature.feature.update';

    public function getName(): string
    {
        return __('permissions.catalog.feature.feature.grants.update');
    }

    public function getPosition(): int
    {
        return 62;
    }
}

