<?php

namespace App\Listeners\Client;

use App\Events\ClientMessengerUpdated;

class MessengerSaving
{

    /**
     * Логируем изменения в Client Phone.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ClientMessengerUpdated $event)
    {
        $messenger = $event->messenger;
        $client = $event->client;

        if ($messenger->value !== $messenger->getOriginal('value')) {
            $client->addActivity('client.messenger.value', [
                'from' => $messenger->getOriginal('value'),
                'to' => $messenger->value,
            ]);
        }
    }
}
