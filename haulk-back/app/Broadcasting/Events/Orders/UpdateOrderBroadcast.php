<?php

namespace App\Broadcasting\Events\Orders;

class UpdateOrderBroadcast extends OrderBroadcast
{
    public const NAME = 'order.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
