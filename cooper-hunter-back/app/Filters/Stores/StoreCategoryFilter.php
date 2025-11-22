<?php

declare(strict_types=1);

namespace App\Filters\Stores;

use App\Filters\BaseModelFilter;
use App\Models\Stores\StoreCategory;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;

/**
 * @mixin StoreCategory
 */
class StoreCategoryFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;

    public const TABLE = StoreCategory::TABLE;
}
