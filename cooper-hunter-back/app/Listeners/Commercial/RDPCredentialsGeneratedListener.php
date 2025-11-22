<?php

namespace App\Listeners\Commercial;

use App\Events\Commercial\RDPCredentialsGeneratedEvent;
use App\Notifications\Commercial\RDPCredentialsNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class RDPCredentialsGeneratedListener implements ShouldQueue
{
    public function handle(RDPCredentialsGeneratedEvent $event): void
    {
        Notification::route('mail', $event->getAccount()->member->getEmailString())
            ->notify(
                new RDPCredentialsNotification($event->getAccount())
            );
    }
}