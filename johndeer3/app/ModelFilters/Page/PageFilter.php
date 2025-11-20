<?php

namespace App\ModelFilters\Page;

use EloquentFilter\ModelFilter;

class PageFilter extends ModelFilter
{
    public function alias($value)
    {
        return $this->where('alias', $value);
    }
}
