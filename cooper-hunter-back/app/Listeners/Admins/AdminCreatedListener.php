<?php

namespace App\Listeners\Admins;

use App\Events\Admins\AdminCreatedEvent;
use App\Notifications\Admins\AdminCreateNotification;
use Illuminate\Support\Facades\Notification;

class AdminCreatedListener
{
    public function handle(AdminCreatedEvent $event): void
    {
        Notification::route('mail', (string)$event->getAdmin()->email)
            ->notify(
                new AdminCreateNotification($event->getAdmin(), $event->getPassword())
            );
    }
}
