<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendReport extends Notification
{
    use Queueable;

    private $link;

    /**
     * Create a new notification instance.
     *
     * @param string $link
     */
    public function __construct($linkForPdf)
    {
        $this->link = $linkForPdf;
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
        $appName = prettyAppName();
        $address = config('mail.from.address');

        return (new MailMessage)
            ->from($address, $appName)
            ->greeting("Report")
            ->subject("You report")
            ->action('Link for report', "{$this->link}")
            ->line('Thank you for using our service!')
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
        return [
            //
        ];
    }
}

