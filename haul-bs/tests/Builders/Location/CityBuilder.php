<?php

namespace Tests\Builders\Location;

use App\Foundations\Modules\Location\Models\City;
use App\Foundations\Modules\Location\Models\State;
use Tests\Builders\BaseBuilder;

class CityBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return City::class;
    }

    function name(string $value): self
    {
        $this->data['name'] = $value;

        return $this;
    }

    function zip(string $value): self
    {
        $this->data['zip'] = $value;

        return $this;
    }

    function state(State $model): self
    {
        $this->data['state_id'] = $model->id;

        return $this;
    }
}
