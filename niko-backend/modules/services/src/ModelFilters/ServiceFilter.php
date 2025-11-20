<?php

namespace WezomCms\Services\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Services\Models\Service;
use WezomCms\Services\Models\ServiceGroup;

/**
 * Class ServiceFilter
 * @package WezomCms\Services\ModelFilters
 * @mixin Service
 */
class ServiceFilter extends ModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        return [
            FilterField::makeName(),
            FilterField::make()
                ->name('service_group_id')
                ->label(__('cms-services::admin.Group'))
                ->type(FilterField::TYPE_SELECT)
                ->options(ServiceGroup::getForSelect(false))
                ->hide(!config('cms.services.services.use_groups'))
                ->class('js-select2'),
            FilterField::published(),
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

    public function serviceGroup($serviceGroupId)
    {
        $this->where('service_group_id', $serviceGroupId);
    }
}
