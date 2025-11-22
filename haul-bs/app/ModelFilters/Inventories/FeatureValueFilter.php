<?php

namespace App\ModelFilters\Inventories;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Traits\Filters\SearchByName;

class FeatureValueFilter extends BaseModelFilter
{
    use SearchByName;

    public function feature(string|int $value): void
    {
        $this->where('feature_id', $value);
    }
}
