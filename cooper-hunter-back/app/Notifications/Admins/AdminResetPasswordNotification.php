<?php

namespace App\Notifications\Admins;

use App\Models\Admins\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Admin $admin,
        public string $password
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
            ->greeting(__('messages.reset_password.greeting', ['name' => $this->admin->getName()]))
            ->subject(__('messages.reset_password.subject'))
            ->line(__('messages.reset_password.line_1', ['password' => $this->password]))
            ->line(__('messages.reset_password.line_2'));
    }
}
