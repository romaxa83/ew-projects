<?php

namespace WezomCms\Promotions\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Promotions\Models\Promotions;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\UserRepository;

class PromotionsFilter extends ModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        return [
            FilterField::make()
                ->name('type')
                ->label(__('cms-promotions::admin.Type'))
                ->class('js-select2')
                ->type(FilterField::TYPE_SELECT)
                ->options(Promotions::getTypeBySelect())
            ,
        ];
    }

    public function type($type)
    {
        $this->where('type', $type);
    }
}
