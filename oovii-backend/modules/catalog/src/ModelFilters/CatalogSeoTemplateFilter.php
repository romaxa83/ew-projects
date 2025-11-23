<?php

namespace WezomCms\Catalog\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Catalog\Models\CatalogSeoTemplate;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;

/**
 * Class CatalogSeoTemplateFilter
 * @package WezomCms\Catalog\ModelFilters
 * @mixin CatalogSeoTemplate
 */
class CatalogSeoTemplateFilter extends ModelFilter implements FilterListFieldsInterface
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

    public function published($published)
    {
        $this->where('published', $published);
    }

    public function name($name)
    {
        $this->whereLike('name', $name);
    }
}
