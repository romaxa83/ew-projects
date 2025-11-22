<?php

namespace App\Filters;

use EloquentFilter\ModelFilter;

abstract class BaseModelFilter extends ModelFilter
{
    protected function toLower(string $value): string
    {
        return mb_convert_case($value, MB_CASE_LOWER);
    }
}
