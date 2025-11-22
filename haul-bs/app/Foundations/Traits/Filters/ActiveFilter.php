<?php

namespace App\Foundations\Traits\Filters;

trait ActiveFilter
{
    public function active($value): void
    {
        $this->where('active', $value);
    }
}

