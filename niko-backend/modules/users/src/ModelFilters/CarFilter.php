<?php

namespace WezomCms\Users\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Users\Models\User;
use WezomCms\Users\Repositories\UserRepository;

/**
 * Class UserFilter
 * @package WezomCms\Users\ModelFilters
 * @mixin User
 */
class CarFilter extends ModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        $user = resolve(UserRepository::class);

        return [
            FilterField::make()
                ->name('user')
                ->label(__('cms-users::admin.Owner'))
                ->class('js-select2')
                ->type(FilterField::TYPE_SELECT)
                ->options($user->forSelect([], 'id', 'full_name', false, false))
            ,
        ];
    }

    public function user($id)
    {
        $this->where('user_id', $id);
    }
}
