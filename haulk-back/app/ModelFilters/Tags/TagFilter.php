<?php

namespace App\ModelFilters\Tags;

use EloquentFilter\ModelFilter;

class TagFilter extends ModelFilter
{
    public function q(string $name): TagFilter
    {
        return $this->whereRaw('lower(name) like ?', ['%' . escapeLike(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }

    public function type(string $type): TagFilter
    {
        return $this->where('type', $type);
    }
}
