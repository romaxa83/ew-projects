<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;

class ClientMessengerUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $messenger;
    public $client;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Client\Messenger $messenger)
    {
        $this->messenger = $messenger;
        $this->client = Client::find($messenger->client_id);
    }

}
