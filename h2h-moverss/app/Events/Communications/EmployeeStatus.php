<?php

namespace App\Events\Communications;

use App\Models\Employee;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmployeeStatus implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(protected Employee $employee)
    {}

    public function broadcastAs()
    {
        return 'employee.change.communication.status';
    }

    public function broadcastWith()
    {
        return [];
    }

    public function broadcastOn()
    {
        $channels = [new Channel('employee.change.communication.status')];

        logger_ringostat("[websocket] employee.change.communication.status");

        return $channels;
    }

}

