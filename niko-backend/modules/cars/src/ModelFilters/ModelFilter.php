<?php

namespace WezomCms\Cars\ModelFilters;

use EloquentFilter\ModelFilter as EloquentModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;

class ModelFilter extends EloquentModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        return [
            new FilterField(['name' => 'model', 'label' => __('cms-cars::admin.Model')]),
            new FilterField(['name' => 'brand', 'label' => __('cms-cars::admin.Brand')]),
        ];
    }

    public function brand($brand)
    {
        $this->related('brand', 'name', 'LIKE', '%' . $brand . '%');
    }

    public function model($model)
    {
        $this->where('car_models.name', 'LIKE', '%' . $model . '%');
    }
}
