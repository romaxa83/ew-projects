<?php

namespace App\Notifications\Mail;

use App\DTO\Admin\AdminDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CredentialsNotification extends Notification
{
    use Queueable;

    private AdminDTO $dto;

    /**
     * Create a new notification instance.
     *
     * @param string $link
     */
    public function __construct(AdminDTO $dto)
    {
        $this->dto = $dto;
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
        return (new MailMessage)
            ->from($notifiable->routes['mail'], prettyAppName())
            ->subject(__('email.credentials.subject'))
            ->view('emails.credentials', [
                'data' => $this->dto
            ])
            ;

//        return (new MailMessage)
//            ->from($notifiable->routes['mail'], prettyAppName())
//            ->greeting(prettyAppName())
//            ->subject(__('email.credentials.subject'))
//            ->line(__('email.credentials.login', ['login' => $this->dto->getEmail()]))
//            ->line(__('email.credentials.password', ['password' => $this->dto->getPassword()]))
//            ;
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

