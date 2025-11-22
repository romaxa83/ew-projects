<?php


namespace App\Broadcasting\Events\Orders;

class RestoreOrderBroadcast extends OrderBroadcast
{

    public const NAME = 'order.restore';

    protected function getName(): string
    {
        return self::NAME;
    }
}
