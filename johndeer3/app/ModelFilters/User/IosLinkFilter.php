<?php

namespace App\ModelFilters\User;

use EloquentFilter\ModelFilter;

class IosLinkFilter extends ModelFilter
{
    public function status($value): self
    {
        return $this->where('status', $value);
    }
}


