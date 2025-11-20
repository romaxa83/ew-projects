<?php

namespace WezomCms\Articles\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Articles\Models\Article;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;

/**
 * Class ArticleFilter
 * @package WezomCms\Articles\ModelFilters
 * @mixin Article
 */
class ArticleFilter extends ModelFilter implements FilterListFieldsInterface
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
        $this->related('translations', 'published', $published);
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
