<?php


namespace App\Broadcasting\Events\Carrier;


use App\Broadcasting\Channels\CarrierChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class CarrierBroadcast implements ShouldBroadcast
{

    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected int $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(CarrierChannel::NAME . $this->companyId)
        ];
    }

    public function broadcastAs(): string
    {
        return $this->getName();
    }

    abstract protected function getName(): string;

}
