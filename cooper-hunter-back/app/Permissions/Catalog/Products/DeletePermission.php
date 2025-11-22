<?php

namespace App\Permissions\Catalog\Products;

use Core\Permissions\BasePermission;

class DeletePermission extends BasePermission
{
    public const KEY = 'catalog.product.delete';

    public function getName(): string
    {
        return __('permissions.catalog.product.grants.delete');
    }

    public function getPosition(): int
    {
        return 43;
    }
}
