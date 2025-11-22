<?php

namespace App\Notifications\Saas\CompanyRegistration;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotConfirmEmailThirdRemind extends Notification
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
            ->subject("Let's Complete Your Haulk Setup - Verify Your Email")
            ->greeting('Hi ' . $notifiable->getFullName() . ',')
            ->line("We're excited to have you on board and want to make sure you're getting the full Haulk experience. Verifying your email is just one click away!")
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
