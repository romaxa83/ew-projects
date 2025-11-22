<?php

namespace App\Traits\Filter;

trait IdFilterTrait
{
    public function id(int $id): void
    {
        $this->where('id', $id);
    }
}
