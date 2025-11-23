<?php

namespace WezomCms\Catalog\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Catalog\Models\Brand;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;

/**
 * Class BrandFilter
 * @package WezomCms\Catalog\ModelFilters
 * @mixin Brand
 */
class BrandFilter extends ModelFilter implements FilterListFieldsInterface
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
        return [
            FilterField::makeName(),
            FilterField::published(),
        ];
    }

    public function name($name): void
    {
        $this->related('translations', 'name', 'LIKE', '%' . Helpers::escapeLike($name) . '%');
    }

    public function published($published): void
    {
        $this->where('published', $published);
    }

    /*public function collection($id): void
    {
        $this->whereHas('products', function ($query) use ($id) {
            $query->whereHas('collections', function($q) use ($id) {
                $q->where('id', $id);
            });
        });
    }

    public function category($id): void
    {
        $this->whereHas('products', function ($query) use ($id) {
            $query->whereHas('category', function($q) use ($id) {
                $q->where('id', $id);
            });
        });
    }*/
}
