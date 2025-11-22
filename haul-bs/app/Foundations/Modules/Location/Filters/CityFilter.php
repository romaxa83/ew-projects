<?php

namespace App\Foundations\Modules\Location\Filters;

use App\Foundations\Models\BaseModelFilter;
use App\Foundations\Traits\Filters\ActiveFilter;

class CityFilter extends BaseModelFilter
{
    use ActiveFilter;

    public function search(string $value): self
    {
        return $this->whereRaw('lower(name) like ?', [escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%']);
    }

    public function zip(string $value)
    {
        return $this->whereRaw('lower(zip) like ?', [escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%']);
    }
}
