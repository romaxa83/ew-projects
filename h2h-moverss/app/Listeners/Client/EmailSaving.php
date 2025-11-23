<?php

namespace App\Listeners\Client;

use App\Events\ClientEmailUpdated;

class EmailSaving
{

    /**
     * Логируем изменения в Client Phone.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ClientEmailUpdated $event)
    {
        $email = $event->email;
        $client = $event->client;

        if ($email->value !== $email->getOriginal('value')) {
            $client->addActivity('client.email.value', [
                'from' => $email->getOriginal('value'),
                'to' => $email->value,
            ]);
        }
    }
}
