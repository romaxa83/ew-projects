<?php

namespace App\ModelFilters\Customers;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Traits\Filters\EmailFilter;
use App\Foundations\Traits\Filters\TypeFilter;
use Illuminate\Database\Eloquent\Builder;

class CustomerFilter extends BaseModelFilter
{
    use TypeFilter;
    use EmailFilter;

    public function forSalesManager(int|string $id): void
    {
        $this->where(function (Builder $query) use ($id) {
            return $query->where('sales_manager_id', $id)
                ->orWhereNull('sales_manager_id');
        });
    }

    public function search(string $name)
    {
        $this->where(
            function (Builder $query) use ($name) {
                return $query
                    ->whereRaw('concat_ws(\' \', lower(first_name), lower(last_name)) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(email) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%'])
                    ->orWhereRaw('lower(phone) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
            }
        );
    }

    public function tag(int|string $value): void
    {
        $this->whereHas('tags',
            fn(Builder $query) => $query->where('id', $value)
        );
    }
}
