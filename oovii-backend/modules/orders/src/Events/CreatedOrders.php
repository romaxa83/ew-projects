<?php

namespace WezomCms\Orders\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CreatedOrders
{
    use SerializesModels;

    public Collection $orders;

    /**
     * CreatedOrder constructor.
     * @param  Collection  $orders
     */
    public function __construct(Collection $orders)
    {
        $this->orders = $orders;
    }
}
