<?php

namespace App\Filters\Sips;

use App\Models\Sips\Sip;
use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Sip
 */
class SipFilter extends ModelFilter
{
    use IdFilterTrait;

    public function hasEmployee(bool $value): void
    {
        if($value){
            $this->has('employee');
        } else {
            $this->doesntHave('employee');
        }
    }

    public function employeeStatuses(array $value): void
    {
        $this->whereHas('employee',
            fn(Builder $b):Builder => $b->whereIn('status', $value)
        );
    }
}
