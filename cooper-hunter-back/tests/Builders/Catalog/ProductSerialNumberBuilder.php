<?php

namespace Tests\Builders\Catalog;

use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use Tests\Builders\BaseBuilder;

class ProductSerialNumberBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return ProductSerialNumber::class;
    }

    public function setProduct(Product $model): self
    {
        $this->data['product_id'] = $model->id;

        return $this;
    }

    public function setSerialNumber(string $value): self
    {
        $this->data['serial_number'] = $value;

        return $this;
    }
}

