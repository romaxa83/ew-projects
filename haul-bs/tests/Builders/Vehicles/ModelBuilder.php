<?php

namespace Tests\Builders\Vehicles;

use App\Models\Vehicles\Make;
use App\Models\Vehicles\Model;
use Tests\Builders\BaseBuilder;

class ModelBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Model::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function make(Make $model): self
    {
        $this->data['make_id'] = $model->id;
        return $this;
    }
}
