<?php

namespace App\Permissions\Catalog\Categories;

use Core\Permissions\BasePermission;

class CategoryImageUploadPermission extends BasePermission
{
    public const KEY = 'catalog.category.image_upload';

    public function getName(): string
    {
        return __('permissions.catalog.category.grants.image_upload');
    }

    public function getPosition(): int
    {
        return 36;
    }
}
