<?php

namespace App\Events\Orders;

use App\Enums\Common\LogKeyEnum;
use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ClientEditInventory implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(protected int $orderId)
    {}

    public function broadcastAs()
    {
        return 'order.client.edit.inventory';
    }

    public function broadcastWith()
    {
        $order = Order::withInventoriesFormat($this->orderId)
            ->findOrFail($this->orderId);

        return [$order];
    }

    public function broadcastOn()
    {
        $channels = [new Channel('order.'.$this->orderId)];

        Log::info( LogKeyEnum::Websocket() . 'ClientEditInventory', [
            'orderId' => $this->orderId
        ]);

        return $channels;
    }
}


