<?php

namespace App\Permissions\Catalog\Features\Specifications;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.feature.specification.update';

    public function getName(): string
    {
        return __('permissions.catalog.feature.specifications.grants.update');
    }

    public function getPosition(): int
    {
        return 62;
    }
}

