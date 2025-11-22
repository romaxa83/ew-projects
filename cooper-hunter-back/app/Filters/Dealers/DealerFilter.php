<?php

namespace App\Filters\Dealers;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;

class DealerFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function company($value): void
    {
        $this->where('company_id', $value);
    }
}
