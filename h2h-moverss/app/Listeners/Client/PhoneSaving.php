<?php

namespace App\Listeners\Client;

use App\Events\ClientPhoneUpdated;

class PhoneSaving
{

    /**
     * Логируем изменения в Client Phone.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ClientPhoneUpdated $event)
    {
        $phone = $event->phone;
        $client = $event->client;

        if ($phone->value !== $phone->getOriginal('value')) {
            $client->addActivity('client.phone.value', [
                'from' => $phone->getOriginal('value'),
                'to' => $phone->value,
            ]);
        }
    }
}
