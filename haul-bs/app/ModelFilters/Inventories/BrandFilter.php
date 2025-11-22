<?php

namespace App\ModelFilters\Inventories;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Traits\Filters\SearchByName;

class BrandFilter extends BaseModelFilter
{
    use SearchByName;
}
