<?php

namespace WezomCms\Core\ModelFilters;

use EloquentFilter\ModelFilter;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Models\Role;

/**
 * Class AdministratorFilter
 * @package WezomCms\Core\ModelFilters
 * @mixin Administrator
 */
class AdministratorFilter extends ModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        $fields = [
            FilterField::makeName()->label(__('cms-core::admin.administrators.Name')),
            FilterField::make()->name('email')->label(__('cms-core::admin.administrators.E-mail'))->size(2),
            FilterField::make()
                ->name('roles')
                ->label(__('cms-core::admin.administrators.Role'))
                ->type(FilterField::TYPE_SELECT)
                ->options(Role::getForSelect(false))
                ->placeholder(__('cms-core::admin.layout.Not set')),
            FilterField::active(),
        ];

        foreach (array_filter(event('administrators.get_filter_fields', compact('fields'))) as $item) {
            $fields = array_merge($fields, $item);
        }

        return $fields;
    }

    public function name($name)
    {
        $this->whereLike('name', $name);
    }

    public function email($email)
    {
        $this->whereLike('email', $email);
    }

    public function active($active)
    {
        $this->where('active', $active);
    }

    public function roles($roles)
    {
        $this->related('roles', 'roles.id', $roles);
    }
}
