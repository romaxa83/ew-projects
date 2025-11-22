<?php

namespace App\Listeners\User;

use App\Events\User\EmailConfirm;
use App\Notifications\Mail\EmailConfirmNotification;

class EmailConfirmListeners
{

    public function handle(EmailConfirm $event)
    {
        try {
            \Notification::route('mail',$event->user->email->getValue())
                ->notify(new EmailConfirmNotification($event->emailVerify));

        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
