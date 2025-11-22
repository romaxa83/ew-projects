<?php

namespace App\Filters\Catalog;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SlugFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class PdfFilter extends ModelFilter
{
    use IdFilterTrait;
}


