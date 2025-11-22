<?php

namespace App\Notifications\Auth;

use App\Foundations\Models\BaseAuthenticatableModel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(
        public BaseAuthenticatableModel $user,
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
            ->greeting(__('mail.reset_password.greeting', ['name' => $this->user->getName()]))
            ->subject(__('mail.reset_password.subject'))
            ->line(new HtmlString(__('mail.reset_password.line_1', ['password' => $this->password])))
            ->line(__('mail.reset_password.line_2'));
    }
}
