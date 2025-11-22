<?php

namespace App\Notifications\Saas\CompanyRegistration;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotConfirmEmailNextDay extends Notification
{
    use Queueable;

    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Trouble Verifying Your Email? We're Here to Help")
            ->greeting('Hi ' . $notifiable->getFullName() . ',')
            ->line("We noticed a hiccup in your email verification process. No worries, these things happen. Click below to retry, and if you need any assistance, we're just a message away.")
            ->action(
                'Confirm email',
                config('frontend.auth_url') . '/email-confirmation?confirmation_hash=' . $this->token,
            )
            ;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
