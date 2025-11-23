<?php

namespace WezomCms\Catalog\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;

class LabelFilter extends ModelFilter implements FilterListFieldsInterface
{
    public $relations = [
        'products' => [
            'product_name' => 'relations_search',
            'collection_id' => 'related_collection',
            'category_id' => 'related_category',
            'with_products' => 'active_product',
            'brand_id' => 'brand',
            'price_from' => 'price_from',
            'price_to' => 'price_to',
            'specifications' => 'specifications',
        ],
    ];

    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        $result = [
            FilterField::id(),
        ];

        $result[] = FilterField::make()
            ->type(FilterField::TYPE_SELECT)
            ->options([
                0 => __('cms-core::admin.filter.no'),
                1 => __('cms-core::admin.filter.yes'),
            ])
            ->name('is_gender')
            ->label(__('cms-catalog::admin.labels.filter_gender'))
            ->class('js-select2');


        $result[] = FilterField::published();

        return $result;
    }

    public function id($id): void
    {
        $this->whereIn('id', explode(',', $id));
    }

    public function published($published): void
    {
        $this->where('published', $published);
    }

    public function isGender($value): void
    {
        $this->where('is_gender', filter_var($value, FILTER_VALIDATE_BOOLEAN));
    }
}

