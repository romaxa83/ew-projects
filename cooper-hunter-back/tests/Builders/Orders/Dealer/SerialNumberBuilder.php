<?php

namespace Tests\Builders\Orders\Dealer;

use App\Models\Catalog\Products\Product;
use App\Models\Orders\Dealer\Order;
use App\Models\Orders\Dealer\SerialNumber;
use Tests\Builders\BaseBuilder;

class SerialNumberBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return SerialNumber::class;
    }

    public function setOrder(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function setSerialNumber(string $value): self
    {
        $this->data['serial_number'] = $value;
        return $this;
    }

    public function setProduct(Product $model): self
    {
        $this->data['product_id'] = $model->id;
        return $this;
    }
}
