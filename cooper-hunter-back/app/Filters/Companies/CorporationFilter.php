<?php

namespace App\Filters\Companies;

use App\Traits\Filter\IdFilterTrait;
use EloquentFilter\ModelFilter;

class CorporationFilter extends ModelFilter
{
    use IdFilterTrait;
}

