<?php

namespace App\Notifications\Admins;

use App\Models\Admins\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminForgotPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Admin $admin,
        protected string $link
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return app(MailMessage::class)
            ->greeting(__('messages.forgot_password.greeting', ['name' => $this->admin->getName()]))
            ->subject(__('messages.forgot_password.subject'))
            ->line(__('messages.forgot_password.line_1'))
            ->line(__('messages.forgot_password.line_2'))
            ->line(__('messages.forgot_password.line_3', ['link' => $this->link]));
    }
}
