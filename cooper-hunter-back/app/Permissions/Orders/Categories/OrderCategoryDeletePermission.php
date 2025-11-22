<?php

namespace App\Permissions\Orders\Categories;

use Core\Permissions\BasePermission;

class OrderCategoryDeletePermission extends BasePermission
{
    public const KEY = 'order.category.delete';

    public function getName(): string
    {
        return __('permissions.order.category.grants.delete');
    }

    public function getPosition(): int
    {
        return 3;
    }
}
