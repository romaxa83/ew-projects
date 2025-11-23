<?php

namespace WezomCms\Catalog\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Catalog\Models\Specifications\SpecValue;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;

/**
 * Class SpecValueFilter
 * @package WezomCms\Catalog\ModelFilters
 * @mixin SpecValue
 */
class SpecValueFilter extends ModelFilter implements FilterListFieldsInterface
{
    public $relations = [
        'products' => [
            'product_name' => 'relations_search',
            'collection_id' => 'related_collection',
            'category_id' => 'related_category',
            'with_products' => 'active_product',
        ],
    ];

    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        return [];
    }
}
