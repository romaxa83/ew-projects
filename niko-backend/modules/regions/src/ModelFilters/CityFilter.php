<?php

namespace WezomCms\Regions\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Regions\Repositories\RegionsRepository;

class CityFilter extends ModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        $region = resolve(RegionsRepository::class);

        return [
            new FilterField(['name' => 'city', 'label' => __('cms-regions::admin.City')]),
            FilterField::make()
                ->name('region')
                ->label(__('cms-regions::admin.Region'))
                ->class('js-select2')
                ->type(FilterField::TYPE_SELECT)
                ->options($region->forSelect())
            ,
        ];
    }

    public function city($city)
    {
        $this->related('translations', 'name', 'LIKE', '%' . $city . '%');
    }

    public function region($id)
    {
        $this->where('region_id', $id);
    }
}

