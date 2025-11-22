<?php

namespace App\Filters\Orders\Categories;

use App\Filters\BaseDictionaryModelFilter;
use App\Models\Orders\Categories\OrderCategory;

/**
 * Class OrderCategoryFilter
 * @package App\Filters\Orders\Categories
 *
 * @mixin OrderCategory
 */
class OrderCategoryFilter extends BaseDictionaryModelFilter
{
    public const TABLE = OrderCategory::TABLE;
}
