<?php

namespace App\Foundations\Traits\Filters;

trait SearchByName
{
    public function search(string $value)
    {
        $searchString = '%' . escape_like(mb_convert_case($value, MB_CASE_LOWER)) . '%';
        $this->whereRaw('lower(name) like ?', [$searchString]);
    }
}

