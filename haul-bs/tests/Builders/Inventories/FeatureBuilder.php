<?php

namespace Tests\Builders\Inventories;

use App\Models\Inventories\Features\Feature;
use Tests\Builders\BaseBuilder;

class FeatureBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Feature::class;
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

    public function position(string $value): self
    {
        $this->data['position'] = $value;
        return $this;
    }

    public function multiple(bool $value): self
    {
        $this->data['multiple'] = $value;
        return $this;
    }
}
