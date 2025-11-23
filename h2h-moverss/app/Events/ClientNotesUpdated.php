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

class ClientNotesUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notes;
    public $client;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Client\Notes $notes)
    {
        $this->notes = $notes;
        $this->client = Client::find($notes->client_id);
    }

}
