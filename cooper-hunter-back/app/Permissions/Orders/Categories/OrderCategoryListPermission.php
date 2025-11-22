<?php

namespace App\Permissions\Orders\Categories;

use Core\Permissions\BasePermission;

class OrderCategoryListPermission extends BasePermission
{

    public const KEY = 'order.category.list';

    public function getName(): string
    {
        return __('permissions.order.category.grants.list');
    }

    public function getPosition(): int
    {
        return 4;
    }
}
