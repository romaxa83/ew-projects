<?php

namespace App\Permissions\Catalog\Products;

use Core\Permissions\BasePermission;

class CreatePermission extends BasePermission
{
    public const KEY = 'catalog.product.create';

    public function getName(): string
    {
        return __('permissions.catalog.product.grants.create');
    }

    public function getPosition(): int
    {
        return 41;
    }
}

