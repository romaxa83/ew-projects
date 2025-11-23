<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Client;

class ClientUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $client;

    /**
     * Создать новый экземпляр события.
     *
     * @param  Client  $client
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

}
