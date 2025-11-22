<?php

namespace App\Notifications\System;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendMessageWithFile extends Notification
{
    use Queueable;

    private $link;

    public function __construct($link)
    {
        $this->link = $link;
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

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $address = env('MAIL_FROM_ADDRESS');

        return (new MailMessage)
            ->from($address, 'SYSTEM')
            ->greeting("Hi")
            ->subject("System Message")
            ->action('Link to file', "{$this->link}")
            ->attach($this->link)
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
        return [];
    }
}

