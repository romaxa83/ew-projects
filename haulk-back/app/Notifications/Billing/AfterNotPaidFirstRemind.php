<?php

namespace App\Notifications\Billing;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AfterNotPaidFirstRemind extends Notification
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
            ->subject(__("email.saas.company.billing.after_not_paid_first_remind.subject"))
            ->greeting(__("email.saas.company.billing.after_not_paid_first_remind.greeting", [
                'name' => $notifiable->getSuperAdmin()->full_name
            ]))
            ->line(__("email.saas.company.billing.after_not_paid_first_remind.body"))
            ;
    }
    public function toArray($notifiable): array
    {
        return [];
    }
}
