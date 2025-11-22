<?php

namespace App\Listeners;

use App\Events\ChangeHashEvent;
use App\Models\Hash;

class ChangeHashListeners
{

    public function handle(ChangeHashEvent $event)
    {
        try {

            Hash::createOrUpdate($event->alias);

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
