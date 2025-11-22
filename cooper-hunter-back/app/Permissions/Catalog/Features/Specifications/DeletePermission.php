<?php

namespace App\Permissions\Catalog\Features\Specifications;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.feature.specification.delete';

    public function getName(): string
    {
        return __('permissions.catalog.feature.specifications.grants.delete');
    }

    public function getPosition(): int
    {
        return 63;
    }
}
