<?php

namespace App\ModelFilters\BodyShop\Inventories;

use App\Models\BodyShop\Inventories\Category;
use EloquentFilter\ModelFilter;

/**
 * Class CategoryFilter
 *
 * @mixin Category
 *
 * @package App\ModelFilters\BodyShop\Inventories
 */
class CategoryFilter extends ModelFilter
{
    public function q(string $name)
    {
        $searchString = '%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%';
        $this->whereRaw('lower(name) like ?', [$searchString]);
    }
}
