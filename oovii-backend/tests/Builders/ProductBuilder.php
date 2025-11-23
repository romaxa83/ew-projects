<?php

namespace Tests\Builders;

use WezomCms\Catalog\Models\Product;

class ProductBuilder
{
    private $name;

    private array $data = [];

    public function setName($value): self
    {
        $this->name = $value;
        $this->data['name'] = $value;

        return $this;
    }

    public function setPopular(): self
    {
        $this->data['popular'] = true;

        return $this;
    }

    public function setBestPrice(): self
    {
        $this->data['best_price'] = true;

        return $this;
    }

    public function setPrices(float $cost, float $cost_discount = 0.0): self
    {
        $this->data['cost'] = $cost;
        $this->data['cost_discount'] = $cost_discount;

        return $this;
    }

    public function create(): Product
    {
        return $this->save();
    }

    private function save(): Product
    {
        return Product::factory()->new($this->data)->create();
    }
}


