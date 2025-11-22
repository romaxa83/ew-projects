<?php

namespace Tests\Builders\Inventories;

use App\Models\Inventories\Unit;
use Tests\Builders\BaseBuilder;

class UnitBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Unit::class;
    }

    public function accept_decimals(bool $value): self
    {
        $this->data['accept_decimals'] = $value;
        return $this;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }
}
