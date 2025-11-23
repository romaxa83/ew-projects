<?php

namespace App\Events\Ringostat;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Traits\ResponseFormatter;

class EmployeeOnCall implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
    use ResponseFormatter;


    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {}

    public function broadcastAs()
    {
        return 'employee.event';
    }

    public function broadcastWith()
    {
        return ['data' => 'test'];
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [new Channel('employee.1')];

        return $channels;
    }

}
