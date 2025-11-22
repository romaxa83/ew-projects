<?php

namespace App\Notifications\Billing;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BlockedAccount extends Notification
{
    use Queueable;

    private string $contactName;

    /**
     * Create a new notification instance.
     * @param string $contactName
     * @return void
     */
    public function __construct(string $contactName)
    {
        $this->contactName = $contactName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your account is blocked')
            ->line('Hello ' . $this->contactName . '!')
            ->line('We were unable to debit money from your card. Please check if there is enough money on the card. ' .
                'Then click the "pay" button in the billing management section to pay the bill.');
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
