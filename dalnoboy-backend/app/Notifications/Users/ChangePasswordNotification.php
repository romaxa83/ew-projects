<?php

namespace App\Notifications\Users;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangePasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $password)
    {
    }

    public function via($notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail(User $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting(trans('passwords.email.greeting', ['name' => $notifiable->getName()]))
            ->subject(trans('passwords.email.change_subject'))
            ->line(trans('passwords.email.password_changed'))
            ->action(trans('passwords.email.login'), config('routes.front.home'))
            ->markdown(
                'notifications::email',
                [
                    'additional_info' => [
                        trans('fields.email') => $notifiable->email,
                        trans('fields.password') => $this->password,
                    ],
                ]
            );
    }
}
