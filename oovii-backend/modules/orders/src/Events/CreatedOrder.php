<?php

namespace WezomCms\Orders\Events;

use Illuminate\Queue\SerializesModels;
use WezomCms\Orders\Models\Order;

class CreatedOrder
{
    use SerializesModels;

    public Order $order;

    /**
     * CreatedOrder constructor.
     * @param  Order  $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }
}
