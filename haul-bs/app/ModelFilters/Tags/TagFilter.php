<?php

namespace App\ModelFilters\Tags;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Traits\Filters\TypeFilter;

class TagFilter extends BaseModelFilter
{
    use TypeFilter;

    public function search(string $name): self
    {
        return $this->whereRaw('lower(name) like ?', ['%' . escape_like(mb_convert_case($name, MB_CASE_LOWER)) . '%']);
    }
}
