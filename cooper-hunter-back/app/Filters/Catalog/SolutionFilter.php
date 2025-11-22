<?php

namespace App\Filters\Catalog;

use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;

/**
 * Class SolutionFilter
 * @package App\Filters\Catalog
 *
 */
class SolutionFilter extends ModelFilter
{
    use IdFilterTrait;

    public function type(string $type): void
    {
        $this->where('type', $type);
    }
}

