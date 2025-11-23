<?php

namespace WezomCms\Orders\Events;

use Illuminate\Queue\SerializesModels;
use WezomCms\Orders\Models\Order;

class CanceledOrder
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
}
