<?php

namespace App\ModelFilters\Feature;

use EloquentFilter\ModelFilter;

class FeatureFilter extends ModelFilter
{
    public function name($value)
    {
        return $this->whereHas('current', fn($q) => $q->where('name', 'like', $value.'%'));
    }

    public function eg($value)
    {
        return $this->whereHas('egs', fn($q) => $q->where('id', $value));
    }

    public function type($value)
    {
        return $this->where('type', $value);
    }
}
