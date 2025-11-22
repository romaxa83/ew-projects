<?php

namespace App\Listeners\Admin;

use App\Events\Admin\GeneratePassword;
use App\Jobs\Mail\SendCredentials;

class GeneratePasswordListeners
{
    public function handle(GeneratePassword $event)
    {
        try {
            SendCredentials::dispatchNow($event->adminDTO);

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
