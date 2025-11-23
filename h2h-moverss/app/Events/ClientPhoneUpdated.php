<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client\Phone;

class ClientPhoneUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Phone
     */
    public $phone;
    public $client;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Phone $phone)
    {
        $this->phone = $phone;
        $this->client = Client::find($phone->client_id);
    }

}
