<?php

namespace App\Traits\Filter;

trait ActiveFilter
{
    public function active(bool $value): void
    {
        $this->where('active', $value);
    }
}
