<?php

namespace App\ModelFilters\JD;

use EloquentFilter\ModelFilter;

class ClientFilter extends ModelFilter
{
    public function search($value)
    {
        return $this->where('company_name', 'like', '%' . $value . '%');
    }
}
