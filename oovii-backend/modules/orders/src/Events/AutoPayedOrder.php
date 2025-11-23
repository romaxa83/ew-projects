<?php

namespace WezomCms\Orders\Events;

use Illuminate\Queue\SerializesModels;
use WezomCms\Orders\Models\Order;

class AutoPayedOrder
{
    use SerializesModels;

    /**
     * @var Order
     */
    public $order;

    /**
     * AutoPayedOrder constructor.
     * @param  Order  $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
