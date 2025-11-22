<?php

namespace App\Notifications\Saas\CompanyRegistration;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyRegistrationConfirmEmail extends Notification
{
    use Queueable;

    private string $confirmation_hash;

    /**
     * Create a new notification instance.
     *
     * @param string $confirmation_hash
     */
    public function __construct(string $confirmation_hash)
    {
        $this->confirmation_hash = $confirmation_hash;
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
        return (new MailMessage())
            ->subject("Your Verified Email! Welcome to the Haulk Experience")
            ->greeting('Hello ' . $notifiable->getFullName() . ',')
            ->line('Great news! Your email verification link. Dive in and discover how we can transform your transportation management.')
            ->action(
                'Confirm email',
                config('frontend.auth_url') . '/email-confirmation?confirmation_hash=' . $this->confirmation_hash,
            );
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
            //
        ];
    }
}
