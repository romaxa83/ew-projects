<?php

namespace App\Permissions\Catalog\Features\Features;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.feature.feature.delete';

    public function getName(): string
    {
        return __('permissions.catalog.feature.feature.grants.delete');
    }

    public function getPosition(): int
    {
        return 63;
    }
}
