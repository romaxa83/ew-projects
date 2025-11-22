<?php

namespace App\Notifications\Saas\CompanyRegistration;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotConfirmEmailSecondRemind extends Notification
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
            ->subject("Quick Reminder: Verify Your Email to Get Started with Haulk")
            ->greeting('Hi ' . $notifiable->getFullName() . ',')
            ->line("Your journey to efficient transportation management awaits! Remember to verify your email to unlock all of Haulk's features.")
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
