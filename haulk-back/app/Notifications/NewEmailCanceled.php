<?php

namespace App\Notifications;

use App\Models\Saas\Company\Company;
use App\Models\Users\ChangeEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEmailCanceled extends Notification
{
    use Queueable;

    private ChangeEmail $changeEmail;
    private ?Company $company;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(ChangeEmail $changeEmail)
    {
        $this->changeEmail = $changeEmail;
        $this->company = $changeEmail->user->getCompany();
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
            ->subject("Email change")
            ->greeting("Hello,")
            ->line('You have canceled your email change request.');
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
