<?php


namespace App\Broadcasting\Events\Orders;


use App\Broadcasting\Channels\OrderChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class OrderBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public int $id;

    protected int $companyId;

    public function __construct(int $orderId, int $companyId)
    {
        $this->id = $orderId;

        $this->companyId = $companyId;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(OrderChannel::NAME . $this->companyId)
        ];
    }

    public function broadcastAs(): string
    {
        return $this->getName();
    }

    abstract protected function getName(): string;
}
