<?php

namespace Tests\Builders\Orders\Parts;

use App\Enums\Orders\Parts\DeliveryStatus;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Order;
use Tests\Builders\BaseBuilder;

class DeliveryBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Delivery::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }

    public function cost(float $value): self
    {
        $this->data['cost'] = $value;
        return $this;
    }

    public function status(DeliveryStatus $value): self
    {
        $this->data['status'] = $value->value;
        return $this;
    }
}
