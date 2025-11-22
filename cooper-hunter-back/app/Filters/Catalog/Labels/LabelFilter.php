<?php

namespace App\Filters\Catalog\Labels;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;

class LabelFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;
}
