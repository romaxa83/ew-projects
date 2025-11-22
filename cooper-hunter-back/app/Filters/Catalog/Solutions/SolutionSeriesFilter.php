<?php

namespace App\Filters\Catalog\Solutions;

use App\Filters\BaseModelFilter;
use App\Models\Catalog\Solutions\Series\SolutionSeries;
use App\Traits\Filter\IdFilterTrait;

/**
 * @mixin SolutionSeries
 */
class SolutionSeriesFilter extends BaseModelFilter
{
    use IdFilterTrait;
}