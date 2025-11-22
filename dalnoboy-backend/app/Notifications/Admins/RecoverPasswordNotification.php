<?php

namespace App\Notifications\Admins;

use App\Models\Admins\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class RecoverPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $link)
    {
        $this->locale = Lang::getLocale();
    }

    public function via(mixed $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail(Admin $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting(trans('messages.forgot_password.greeting', ['name' => $notifiable->getName()]))
            ->subject(trans('messages.forgot_password.subject'))
            ->line(trans('messages.forgot_password.line_1'))
            ->line(trans('messages.forgot_password.line_2'))
            ->action(trans('messages.forgot_password.line_3'), $this->link);
    }
}
