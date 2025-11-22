<?php

namespace App\Notifications\Saas\CompanyRegistration;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotConfirmEmailFinalRemind extends Notification
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
            ->subject("Final Reminder: Verify Your Email to Activate Your Haulk Account")
            ->greeting('Hi ' . $notifiable->getFullName() . ',')
            ->line("Don't miss out! This is your last reminder to verify your email and start transforming your transportation management with Haulk.")
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
