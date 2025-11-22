<?php


namespace App\Broadcasting\Events\DriverTripReport;

use App\Broadcasting\Channels\DriverTripReportChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class DriverTripReportBroadcast implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public int $id;

    protected int $companyId;

    public function __construct(int $driverTripReportId, int $companyId)
    {
        $this->id = $driverTripReportId;

        $this->companyId = $companyId;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(DriverTripReportChannel::NAME . $this->companyId)
        ];
    }

    public function broadcastAs(): string
    {
        return $this->getName();
    }

    abstract protected function getName(): string;

}
