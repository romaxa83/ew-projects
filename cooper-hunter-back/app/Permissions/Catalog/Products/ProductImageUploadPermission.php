<?php

namespace App\Permissions\Catalog\Products;

use Core\Permissions\BasePermission;

class ProductImageUploadPermission extends BasePermission
{
    public const KEY = 'catalog.product.image_upload';

    public function getName(): string
    {
        return __('permissions.catalog.product.grants.image_upload');
    }

    public function getPosition(): int
    {
        return 46;
    }
}
