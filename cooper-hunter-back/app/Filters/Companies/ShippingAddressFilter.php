<?php

namespace App\Filters\Companies;

use App\Traits\Filter\ActiveFilterTrait;
use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;

class ShippingAddressFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;
    use ActiveFilterTrait;


    public function company($value): self
    {
        if(is_array($value)){
            return $this->whereIn('company_id', $value);
        }

        return $this->where('company_id', $value);
    }
}
