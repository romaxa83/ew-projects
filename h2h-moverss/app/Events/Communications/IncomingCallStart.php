<?php

namespace App\Events\Communications;

use App\Http\Resources\Calls\IncomingCalResource;
use App\Http\Resources\Clients\ClientResource;
use App\Models\Calls\IncomingCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class IncomingCallStart implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(protected IncomingCall $model)
    {}

    public function broadcastAs()
    {
        return 'incoming.call.start';
    }

    public function broadcastWith()
    {
        $res = IncomingCalResource::make($this->model)
            ->toArray(new Request ());

        if($this->model->client){
            $res['client'] = ClientResource::make($this->model->client)
                ->toArray(new Request ());
        } else {
            $res['client'] = null;
        }

        logger_ringostat("[websocket] incoming.call.start", $res);

        return $res;
    }

    public function broadcastOn()
    {
        $channels = [new Channel('incoming.call.start')];

        return $channels;
    }
}

