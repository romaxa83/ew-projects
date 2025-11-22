<?php


namespace App\Broadcasting\Events\Orders;

class DeleteOrderBroadcast extends OrderBroadcast
{

    public const NAME = 'order.delete';

    protected function getName(): string
    {
        return self::NAME;
    }
}
