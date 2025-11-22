<?php

namespace App\Notifications\Users;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class SendPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $password)
    {
        $this->locale = Lang::getLocale();
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
            ->subject(trans('passwords.email.registration_subject'))
            ->greeting(trans('messages.default-greeting'))
            ->line(trans('passwords.email.greeting', ['name' => $notifiable->getName()]))
            ->line(trans('passwords.email.registration_success'))
            ->action(trans('passwords.email.login'), config('routes.front.thank_you'))
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
