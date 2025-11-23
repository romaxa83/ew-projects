<?php

namespace WezomCms\Catalog\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Catalog\Models\Category;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;

/**
 * Class CategoryFilter
 * @package WezomCms\Catalog\ModelFilters
 * @mixin Category
 */
class CategoryFilter extends ModelFilter implements FilterListFieldsInterface
{
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

    public function name($name)
    {
        $this->related('translations', 'name', 'LIKE', '%' . Helpers::escapeLike($name) . '%');
    }

    public function published($published)
    {
        $this->where('published', $published);
    }

    public function collection($id)
    {
        $this->whereHas('products', function ($query) use ($id) {
            $query->whereHas('collections', function($q) use ($id) {
                $q->where('id', $id);
            });
        });
    }
}
