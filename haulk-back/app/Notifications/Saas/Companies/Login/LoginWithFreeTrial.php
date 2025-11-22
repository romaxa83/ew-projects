<?php

namespace App\Notifications\Saas\Companies\Login;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginWithFreeTrial extends Notification
{
    use Queueable;

    public function __construct()
    {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        /** @var $notifiable User */
        return (new MailMessage())
            ->subject(__("email.saas.company.login.with_free_trial.subject"))
            ->greeting(__("email.saas.company.login.with_free_trial.greeting", [
                'name' => $notifiable->full_name
            ]))
            ->line(__("email.saas.company.login.with_free_trial.body"))
            ;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
