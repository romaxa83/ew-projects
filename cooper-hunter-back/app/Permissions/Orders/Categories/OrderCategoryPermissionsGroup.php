<?php

namespace App\Permissions\Orders\Categories;

use Core\Permissions\BasePermissionGroup;

class OrderCategoryPermissionsGroup extends BasePermissionGroup
{
    public const KEY = 'order.category';

    public function getName(): string
    {
        return __('permissions.order.category.group');
    }

    public function getPosition(): int
    {
        return 60;
    }
}
