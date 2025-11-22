<?php


namespace App\Events\Orders;


use App\Models\Orders\Order;

class OrderDeletedEvent
{
    public function __construct(private Order $order)
    {
    }

    public function getOrder(): Order
    {
        return $this->order;
    }
}
