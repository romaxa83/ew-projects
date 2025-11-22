<?php

namespace App\Permissions\Catalog\Products;

use Core\Permissions\BasePermission;

class ListPermission extends BasePermission
{
    public const KEY = 'catalog.product.list';

    public function getName(): string
    {
        return __('permissions.catalog.product.grants.list');
    }

    public function getPosition(): int
    {
        return 44;
    }
}
