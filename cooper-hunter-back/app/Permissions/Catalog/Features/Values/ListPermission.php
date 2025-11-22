<?php

namespace App\Permissions\Catalog\Features\Values;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.feature.value.list';

    public function getName(): string
    {
        return __('permissions.catalog.feature.value.grants.list');
    }

    public function getPosition(): int
    {
        return 54;
    }
}
