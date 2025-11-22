<?php

namespace App\Notifications\Users;

use App\Models\Customers\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserTaxExemptionNotification extends Notification
{
    use Queueable;

    public function __construct(protected Customer $customer, protected string $link)
    {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('New tax exemption for review')
            ->greeting('Hello!')
            ->greeting(sprintf('New tax exemption was submitted by customer %s on Haulk Depot website.', $this->customer->full_name))
            ->action('Review', $this->link);
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
