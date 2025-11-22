<?php

namespace App\Permissions\Orders\Categories;

use Core\Permissions\BasePermission;

class OrderCategoryCreatePermission extends BasePermission
{
    public const KEY = 'order.category.create';

    public function getName(): string
    {
        return __('permissions.order.category.grants.create');
    }

    public function getPosition(): int
    {
        return 1;
    }
}
