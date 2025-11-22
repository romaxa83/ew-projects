<?php

namespace App\Foundations\Traits\Filters;

trait StatusFilter
{
    public function status(array|string $value): void
    {
        if(is_array($value)){
            $this->whereIn('status', $value);
            return;
        }

        $this->where('status', $value);
    }

    public function statuses(array $value): void
    {
        $this->whereIn('status', $value);
    }
}
