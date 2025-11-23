<?php

namespace WezomCms\Providers\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;
use WezomCms\Core\Contracts\Filter\FilterListFieldsInterface;
use WezomCms\Core\Filter\FilterField;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Providers\Types\ProviderStatus;

class ProviderFilter extends ModelFilter implements FilterListFieldsInterface
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

        $status = FilterField::make()
            ->name('status')
            ->label(__('cms-core::admin.layout.Status'))
            ->size(2)
            ->type(FilterField::TYPE_SELECT)
            ->options(ProviderStatus::list());

        return [
            FilterField::makeName(['label' => __('cms-users::admin.Full name')]),
            FilterField::make()->name('phone')->label(__('cms-users::admin.Phone'))->size(2),
            FilterField::make()->name('company')->label(__('cms-providers::admin.company.Company'))->size(2),
            $email,
            $status,
            FilterField::active(),
            FilterField::make()
                ->name('created_at')
                ->label(__('cms-core::admin.layout.Created at'))
                ->type(FilterField::TYPE_DATE_RANGE),
        ];
    }

    public function name($name)
    {
        $this->where(function ($query) use ($name) {
            $query->where('name', 'LIKE', '%' . Helpers::escapeLike($name) . '%');
        });
    }

    public function company($company)
    {
        $this->where(function ($query) use ($company) {
            $query->where('company', 'LIKE', '%' . Helpers::escapeLike($company) . '%');
        });
    }

    public function phone($phone)
    {
        $this->where(function ($query) use ($phone) {
            $query->where('phone', 'LIKE', '%' . Helpers::escapeLike($phone) . '%');
        });
//        $this->whereRaw(
//            'REPLACE(REPLACE(REPLACE(REPLACE(phone, "+", ""), "(", ""), ")", ""), " ", "") LIKE ?',
//            '%' . preg_replace('/[^\d]/', '', $phone) . '%'
//        );
    }

    public function email($email)
    {
        $this->whereLike('email', $email);
    }

    public function active($active)
    {
        $this->where('active', $active);
    }

    public function status($status)
    {
        $this->where('status', $status);
    }
    public function createdAtFrom($date)
    {
        $this->where('created_at', '>=', Carbon::parse($date));
    }

    public function createdAtTo($date)
    {
        $this->where('created_at', '<=', Carbon::parse($date)->endOfDay());
    }
}
