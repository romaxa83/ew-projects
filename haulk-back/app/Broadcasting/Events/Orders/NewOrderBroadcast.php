<?php


namespace App\Broadcasting\Events\Orders;

class NewOrderBroadcast extends OrderBroadcast
{

    public const NAME = 'order.create';

    protected function getName(): string
    {
        return self::NAME;
    }
}
