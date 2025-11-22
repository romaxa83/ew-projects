<?php

namespace App\Permissions\Catalog\Features\Values;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.feature.value.delete';

    public function getName(): string
    {
        return __('permissions.catalog.feature.value.grants.delete');
    }

    public function getPosition(): int
    {
        return 53;
    }
}
