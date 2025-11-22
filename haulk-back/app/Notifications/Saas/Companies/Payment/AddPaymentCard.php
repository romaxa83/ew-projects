<?php

namespace App\Notifications\Saas\Companies\Payment;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AddPaymentCard extends Notification
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
            ->subject(__("email.saas.company.payment_card.add.subject"))
            ->greeting(__("email.saas.company.payment_card.add.greeting", [
                'name' => $notifiable->getSuperAdmin()->full_name
            ]))
            ->line(__("email.saas.company.payment_card.add.body"))
            ;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
