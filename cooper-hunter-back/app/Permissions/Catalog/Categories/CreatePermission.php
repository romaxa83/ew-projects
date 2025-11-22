<?php

namespace App\Permissions\Catalog\Categories;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.category.create';

    public function getName(): string
    {
        return __('permissions.catalog.category.grants.create');
    }

    public function getPosition(): int
    {
        return 31;
    }
}

