<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestNotifications extends Notification
{
    use Queueable;
    /**
     * Создание нового экземпляра уведомления
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Получение каналов доставки уведомления
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }
    /**
     * Получение шаблона почтового уведомления
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->from($notifiable->routes['mail'], prettyAppName())
            ->greeting(prettyAppName())
            ->subject('Tester')
            ->line('Test email');
    }
    /**
     * Получение массива уведомления
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
