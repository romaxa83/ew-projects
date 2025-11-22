<?php

namespace App\Permissions\Catalog\Products;

use Core\Permissions\BasePermission;

class UpdatePermission extends BasePermission
{
    public const KEY = 'catalog.product.update';

    public function getName(): string
    {
        return __('permissions.catalog.product.grants.update');
    }

    public function getPosition(): int
    {
        return 42;
    }
}

