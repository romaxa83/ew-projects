<?php

namespace App\Permissions\Orders\Categories;

use Core\Permissions\BasePermission;

class OrderCategoryUpdatePermission extends BasePermission
{
    public const KEY = 'order.category.update';

    public function getName(): string
    {
        return __('permissions.order.category.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}
