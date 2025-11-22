<?php

namespace App\Traits\Filter;

trait ActiveFilterTrait
{
    public function active(bool $active): void
    {
        $this->where('active', $active);
    }

    public function published(bool $published): void
    {
        $this->where('active', $published);
    }
}
