<?php

namespace App\Permissions\Catalog\Categories;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.category.update';

    public function getName(): string
    {
        return __('permissions.catalog.category.grants.update');
    }

    public function getPosition(): int
    {
        return 32;
    }
}
