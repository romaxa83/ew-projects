<?php

namespace App\Permissions\Catalog\Features\Specifications;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.feature.specification.create';

    public function getName(): string
    {
        return __('permissions.catalog.feature.specifications.grants.create');
    }

    public function getPosition(): int
    {
        return 61;
    }
}

