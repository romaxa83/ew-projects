<?php

namespace WezomCms\Pages\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Pages\Models\Page;

/**
 * Class PageFilter
 * @package WezomCms\Pages\ModelFilters
 * @mixin Page
 */
class PageFilter extends ModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with filter fields.
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
        $this->related('translations', 'name', 'LIKE', '%' . Helpers::escapeLike($name) . '%');
    }

    public function locale($locale)
    {
        $this->related('translations', 'locale', $locale);
    }
}
