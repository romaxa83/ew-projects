<?php

namespace Tests\Builders\Inventories;

use App\Models\Inventories\Brand;
use Tests\Builders\BaseBuilder;

class BrandBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Brand::class;
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
}
