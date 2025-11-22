<?php

namespace App\Permissions\Catalog\Categories;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.category.list';

    public function getName(): string
    {
        return __('permissions.catalog.category.grants.list');
    }

    public function getPosition(): int
    {
        return 34;
    }
}
