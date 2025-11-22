<?php

namespace App\ModelFilters\TypeOfWorks;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Traits\Filters\SearchByName;
use App\Models\TypeOfWorks\TypeOfWork;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin TypeOfWork
 */
class TypeOfWorkFilter extends BaseModelFilter
{
    use SearchByName;

    public function inventory(int|string $value)
    {
        $this->whereHas('inventories',
            fn(Builder $q) => $q->where('inventory_id', $value)
        );
    }
}
