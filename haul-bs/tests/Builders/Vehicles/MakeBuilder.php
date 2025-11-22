<?php

namespace Tests\Builders\Vehicles;

use App\Models\Vehicles\Make;
use Tests\Builders\BaseBuilder;

class MakeBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Make::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }
}
