<?php

namespace App\ModelFilters\Vehicles;

use App\Foundations\Models\BaseModelFilter;

class MakeFilter extends BaseModelFilter
{

    public function search(string $value): self
    {
        return $this->whereRaw('lower(name) like ?', ['%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%']);
    }
}
