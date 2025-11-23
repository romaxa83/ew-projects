<?php

namespace WezomCms\AmoCrm\Listeners;

use WezomCms\AmoCrm\Jobs\AmoSubmitOrderToCrmJob;
use WezomCms\Orders\Events\CreatedOrder;
use WezomCms\Orders\Events\CreatedOrders;

class AmoSubmitOrderToCrmListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param CreatedOrder $event
     * @return void
     */
    public function handle(CreatedOrders $event)
    {
        foreach ($event->orders as $order) {
            dispatch(new AmoSubmitOrderToCrmJob($order->getKey()));
        }
    }
}
