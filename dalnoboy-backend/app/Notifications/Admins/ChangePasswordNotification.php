<?php

namespace App\Notifications\Admins;

use App\Models\Admins\Admin;
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

    public function toMail(Admin $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(trans('passwords.email.admin_change_subject'))
            ->greeting(trans('passwords.email.greeting', ['name' => $notifiable->getName()]))
            ->line(trans('passwords.email.password_changed'))
            ->action(trans('passwords.email.login'), config('routes.front.admin_login'))
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
