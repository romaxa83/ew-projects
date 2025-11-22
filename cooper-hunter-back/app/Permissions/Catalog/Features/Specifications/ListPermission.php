<?php

namespace App\Permissions\Catalog\Features\Specifications;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.feature.specification.list';

    public function getName(): string
    {
        return __('permissions.catalog.feature.specifications.grants.list');
    }

    public function getPosition(): int
    {
        return 64;
    }
}
