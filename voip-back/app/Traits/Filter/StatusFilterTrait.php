<?php

namespace App\Traits\Filter;

trait StatusFilterTrait
{
    public function status(string $value): void
    {
        $this->where('status', $value);
    }

    public function statuses(array $values): void
    {
        $this->whereIn('status', $values);
    }
}

