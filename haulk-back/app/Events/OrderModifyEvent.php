<?php

namespace App\Events;

use App\Listeners\Orders\OrderModifyListener;
use App\Models\Orders\Order;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * @see OrderModifyListener::handle()
 * @method static array|null dispatch(int $orderId)
 */
class OrderModifyEvent
{
    use Dispatchable;

    private int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getOrder(): ?Order
    {
        return Order::withoutGlobalScopes()
            ->with(
                [
                    'payment',
                    'paymentStages',
                    'bonuses',
                    'expenses',
                    'vehicles'
                ]
            )
            ->find($this->orderId);
    }
}
