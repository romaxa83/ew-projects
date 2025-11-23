<?php

namespace App\Listeners\Client;

use App\Events\ClientNotesUpdated;

class NotesSaving
{

    /**
     * Логируем изменения в Client Phone.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ClientNotesUpdated $event)
    {
        $notes = $event->notes;
        $client = $event->client;

        if ($notes->value !== $notes->getOriginal('value')) {
            $client->addActivity('client.notes.value', [
                'from' => $notes->getOriginal('value'),
                'to' => $notes->value,
            ]);
        }
    }
}
