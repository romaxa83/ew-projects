<?php

namespace App\Filters\Catalog\Certificates;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;

class TypeFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function type(string $type): void
    {
        $type = strtolower($type);

        $this->whereRaw('LOWER(`type`) LIKE ?', ["%$type%"]);
    }
}


