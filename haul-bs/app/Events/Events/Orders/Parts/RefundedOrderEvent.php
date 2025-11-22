<?php

namespace App\Events\Events\Orders\Parts;

use App\Models\Orders\Parts\Order;

class RefundedOrderEvent
{
    public function __construct(
        protected Order $model
    )
    {}

    public function getModel(): Order
    {
        return $this->model;
    }
}
