<?php

namespace App\Listeners\Client;

use App\Events\ClientUpdated;

class Saving
{

    /**
     * Логируем изменения в Order.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ClientUpdated $event)
    {
        $client = $event->client;
        if ($client->name !== $client->getOriginal('name')) {
            $client->addActivity('name', [
                'from' => $client->getOriginal('name'),
                'to' => $client->name,
            ]);
        }
        if ($client->lname !== $client->getOriginal('lname')) {
            $client->addActivity('lname', [
                'from' => $client->getOriginal('lname'),
                'to' => $client->lname,
            ]);
        }
    }
}
