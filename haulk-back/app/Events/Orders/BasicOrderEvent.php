<?php

namespace App\Events\Orders;

use App\Models\Orders\Order;

abstract class BasicOrderEvent
{
    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
