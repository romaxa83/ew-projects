<?php

namespace App\Foundations\Models;

use App\Foundations\Traits\Filters\IdFilter;
use App\Foundations\Traits\Filters\SortFilter;
use EloquentFilter\ModelFilter;

abstract class BaseModelFilter extends ModelFilter
{
    use SortFilter;
    use IdFilter;
}
