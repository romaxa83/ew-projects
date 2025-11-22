<?php

namespace App\Filters\Companies;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;

class CompanyFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function status($value): void
    {
        $this->where('status', $value);
    }

    public function corporation($value): void
    {
        $this->where('corporation_id', $value);
    }
}
