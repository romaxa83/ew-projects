<?php

namespace App\Permissions\Catalog\Categories;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.category.delete';

    public function getName(): string
    {
        return __('permissions.catalog.category.grants.delete');
    }

    public function getPosition(): int
    {
        return 33;
    }
}
