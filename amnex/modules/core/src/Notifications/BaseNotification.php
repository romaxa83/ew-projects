<?php

namespace Wezom\Core\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    abstract protected function subject($notifiable): ?string;

    protected function greeting($notifiable): ?string
    {
        return null;
    }

    protected function subTitle($notifiable): ?string
    {
        return null;
    }

    protected function fields($notifiable): array
    {
        return [];
    }

    protected function urlToAdmin($notifiable): ?string
    {
        return null;
    }

    protected function link($notifiable): ?string
    {
        return null;
    }

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

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->markdown('core::mail.notifications.email', [
                'fields' => $this->fields($notifiable),
                'subTitle' => $this->subTitle($notifiable),
                'urlToAdmin' => $this->urlToAdmin($notifiable),
            ])
            ->subject($this->subject($notifiable))
            ->greeting($this->greeting($notifiable));
    }
}
