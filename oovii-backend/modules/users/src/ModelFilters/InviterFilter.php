<?php

namespace WezomCms\Users\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Users\Models\Inviter;

/**
 * Class InviterFilter
 * @package WezomCms\Users\ModelFilters
 * @mixin Inviter
 */
class InviterFilter extends ModelFilter implements FilterListFieldsInterface
{
    /**
     * Generate array with fields
     * @return iterable|FilterField[]
     */
    public function getFields(): iterable
    {
        $email = FilterField::make()
            ->name('email')
            ->label(__('cms-users::admin.E-mail'))
            ->size(2);

        return [
            FilterField::makeName(['label' => __('cms-users::admin.Full name')]),
            FilterField::make()->name('phone')->label(__('cms-users::admin.Phone'))->size(2),
            $email,
            FilterField::active(),
            FilterField::make()
                ->name('created_at')
                ->label(__('cms-core::admin.layout.Created at'))
                ->type(FilterField::TYPE_DATE_RANGE),
        ];
    }

    public function name($name): void
    {
        $this->where(function ($query) use ($name) {
            $query->where('name', 'LIKE', '%' . Helpers::escapeLike($name) . '%')
                ->orWhere('surname', 'LIKE', '%' . Helpers::escapeLike($name) . '%')
                ->orWhere('patronymic', 'LIKE', '%' . Helpers::escapeLike($name) . '%')
                ->orWhere(
                    \DB::raw('CONCAT_WS(" ", `name`, `surname`, `patronymic`)'),
                    'LIKE',
                    '%' . Helpers::escapeLike($name) . '%'
                );
        });
    }

    public function phone($phone): void
    {
        $this->where(function ($query) use ($phone) {
            $query->where('phone', 'LIKE', '%' . Helpers::escapeLike($phone) . '%');
        });
    }

    public function email($email): void
    {
        $this->whereLike('email', $email);
    }

    public function active($active): void
    {
        $this->where('active', $active);
    }

    public function createdAtFrom($date): void
    {
        $this->where('created_at', '>=', Carbon::parse($date));
    }

    public function createdAtTo($date): void
    {
        $this->where('created_at', '<=', Carbon::parse($date)->endOfDay());
    }
}
