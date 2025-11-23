<?php

namespace Tests\Builders\Orders;

use App\Models\Order;
use Tests\Builders\BaseBuilder;

class CustomExtraBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Order\CustomExtra::class;
    }

    public function order(Order $model): self
    {
        $this->data['order_id'] = $model->id;
        return $this;
    }
}
