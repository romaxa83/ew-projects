<?php

namespace App\Notifications\Mail;

use App\DTO\Admin\AdminDTO;
use App\Models\Verify\EmailVerify;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailConfirmNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param string $link
     */
    public function __construct(protected EmailVerify $emailVerify)
    {}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from($notifiable->routes['mail'], prettyAppName())
            ->greeting(prettyAppName())
            ->subject(__('email.confirm_email.subject'))
            ->line(__('email.confirm_email.line 1'))
            ->line(__('email.confirm_email.line 2'))
            ->action(__('email.confirm_email.button'), $this->emailVerify->getLinkConfirm())
            ;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
