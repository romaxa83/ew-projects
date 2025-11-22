<?php

namespace Tests\Builders\Inventories;

use App\Models\Inventories\Features\Feature;
use App\Models\Inventories\Features\Value;
use Tests\Builders\BaseBuilder;

class FeatureValueBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Value::class;
    }

    public function feature(Feature $model): self
    {
        $this->data['feature_id'] = $model->id;
        return $this;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function slug(string $value): self
    {
        $this->data['slug'] = $value;
        return $this;
    }

    public function position(int $value): self
    {
        $this->data['position'] = $value;
        return $this;
    }
}
