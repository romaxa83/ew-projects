<?php

namespace App\Foundations\Traits\Filters;

use Illuminate\Database\Query\Builder;

/**
 * @method static self|Builder filter(array $attributes, string $filterClass = null)
 */
trait Filterable
{
    use \EloquentFilter\Filterable;
}

