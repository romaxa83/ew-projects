<?php

namespace Tests\Builders\Orders\Parts;

use App\Enums\Orders\Parts\ShippingMethod;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Shipping;
use Tests\Builders\BaseBuilder;

class ShippingBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Shipping::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function name(ShippingMethod $value): self
    {
        $this->data['method'] = $value;
        return $this;
    }

    public function terms(string $value): self
    {
        $this->data['terms'] = $value;
        return $this;
    }

    public function cost(float $value): self
    {
        $this->data['cost'] = $value;
        return $this;
    }
}

