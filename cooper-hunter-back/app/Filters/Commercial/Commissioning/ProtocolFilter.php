<?php

namespace App\Filters\Commercial\Commissioning;

use App\Filters\BaseModelFilter;
use App\Traits\Filter\IdFilterTrait;

class ProtocolFilter extends BaseModelFilter
{
    use IdFilterTrait;

    public function type(string $value): void
    {
        $this->where('type', $value);
    }
}

