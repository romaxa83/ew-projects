<?php

namespace App\Notifications\Saas\Companies\Login;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotLoginFinalRemind extends Notification
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
        /** @var $notifiable Company */
        return (new MailMessage())
            ->subject(__("email.saas.company.login.not_free_trial_final_remind.subject"))
            ->greeting(__("email.saas.company.login.not_free_trial_final_remind.greeting", [
                'name' => $notifiable->getSuperAdmin()->full_name
            ]))
            ->line(__("email.saas.company.login.not_free_trial_final_remind.body"))
            ;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
