<?php

namespace App\Broadcasting\Events\GPS\Device\Request;

use App\Broadcasting\Channels\GPS\Device\Request\DeviceRequestChannel;
use App\Broadcasting\Events\GPS\Device\DeviceRequestBroadcast;
use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Saas\GPS\DeviceRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChangeStatusBroadcast extends DeviceRequestBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected DeviceRequest $deviceRequest;
    protected DeviceRequestStatus $status;

    public const NAME = 'device-request.change-status';

    public function __construct(
        DeviceRequest $deviceRequest,
        DeviceRequestStatus $status
    )
    {
        parent::__construct($deviceRequest);

        $this->deviceRequest = $deviceRequest;
        $this->status = $status;
    }
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(DeviceRequestChannel::getNameForUser($this->user));
    }

    public function broadcastWith()
    {
        $msg = null;
        if($this->status->isInWork()){
            $msg = __('notification.device.for_crm.take_request');
        }
        if($this->status->isClosed()){
            $msg = __('notification.device.for_crm.close_request');
        }

        return [
            'msg' => $msg,
            'status' => $this->status->value,
        ];
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
