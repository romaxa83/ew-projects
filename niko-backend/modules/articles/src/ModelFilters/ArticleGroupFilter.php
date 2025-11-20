<?php

namespace WezomCms\Articles\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Articles\Models\ArticleGroup;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;

/**
 * Class ArticleGroupFilter
 * @package WezomCms\Articles\ModelFilters
 * @mixin ArticleGroup
 */
class ArticleGroupFilter extends ModelFilter implements FilterListFieldsInterface
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
            FilterField::locale(),
        ];
    }

    public function published($published)
    {
        $this->where('published', $published);
    }

    public function name($name)
    {
        $this->related('translations', 'name', 'LIKE', '%' . $name . '%');
    }

    public function locale($locale)
    {
        $this->related('translations', 'locale', $locale);
    }
}
