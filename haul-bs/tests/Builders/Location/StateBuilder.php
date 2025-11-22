<?php

namespace Tests\Builders\Location;

use App\Foundations\Modules\Location\Models\State;
use Tests\Builders\BaseBuilder;

class StateBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return State::class;
    }

    function name(string $value): self
    {
        $this->data['name'] = $value;

        return $this;
    }

    function short(string $value): self
    {
        $this->data['state_short_name'] = $value;

        return $this;
    }
}
