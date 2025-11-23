<?php

namespace WezomCms\Catalog\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Catalog\Models\Specifications\Specification;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;

/**
 * Class SpecificationFilter
 * @package WezomCms\Catalog\ModelFilters
 * @mixin Specification
 */
class SpecificationFilter extends ModelFilter implements FilterListFieldsInterface
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

    public function id($value): void
    {
        $this->where('id', $value);
    }

    public function type($value): void
    {
        $this->where('type', $value);
    }

    /*public function collection($value): void
    {
        $this->whereHas('products', function($query) use ($value) {
            $query->whereHas('collections', function($q) use ($value) {
                $q->where('collection_id', $value);
            });
        });
    }

    public function category($value): void
    {
        $this->whereHas('products', function($query) use ($value) {
                $query->whereHas('category', function($q) use ($value) {
                    $q->where('category_id', $value);
                });
            });
    }*/

    public function name($name): void
    {
        $this->related('translations', 'name', 'LIKE', '%' . Helpers::escapeLike($name) . '%');
    }

    public function published($published): void
    {
        $this->where('published', $published);
    }
}
