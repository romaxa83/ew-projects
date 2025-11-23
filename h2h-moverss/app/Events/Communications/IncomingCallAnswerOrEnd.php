<?php

namespace App\Events\Communications;

use App\Models\Calls\IncomingCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomingCallAnswerOrEnd implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(protected IncomingCall $model)
    {}

    public function broadcastAs()
    {
        return 'incoming.call.end';
    }

    public function broadcastWith()
    {
        return ['id' => $this->model->id];
    }

    public function broadcastOn()
    {
        $channels = [new Channel('incoming.call.end')];

        logger_ringostat("[websocket] incoming.call.end", $this->model->toArray());

        return $channels;
    }
}

