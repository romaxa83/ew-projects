<?php

declare(strict_types=1);

namespace App\Filters\Stores;

use App\Filters\BaseModelFilter;
use App\Models\Stores\Store;
use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;

/**
 * @mixin Store
 */
class StoreFilter extends BaseModelFilter
{
    use IdFilterTrait;
    use ActiveFilterTrait;

    public const TABLE = Store::TABLE;

    public function storeCategory(int $id): void
    {
        $this->where('store_category_id', $id);
    }
}
