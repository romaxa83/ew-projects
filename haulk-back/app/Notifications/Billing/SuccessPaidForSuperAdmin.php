<?php

namespace App\Notifications\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuccessPaidForSuperAdmin extends Notification
{
    use Queueable;

    private Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $name = $this->invoice->company->getSuperAdmin()->full_name;
        return (new MailMessage)
            ->subject('Your payment was successful')
            ->line('Hello ' . $name . '!')
            ->line('Thank you for your payment!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
        ];
    }
}
