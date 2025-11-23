<?php

namespace WezomCms\Catalog\ModelFilters;

use WezomCms\Catalog\Repositories\ProductRepository;

/**
 * Class TrashedProductFilter
 * @package WezomCms\Catalog\ModelFilters
 */
class TrashedProductFilter extends ProductFilter
{
    protected function getProductGroups(): array
    {
        $productRepo = app(ProductRepository::class);

        return $productRepo->groupKeyForFilter(true);
    }
}
