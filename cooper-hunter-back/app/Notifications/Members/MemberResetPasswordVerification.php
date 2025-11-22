<?php

namespace App\Notifications\Members;

use App\Models\BaseAuthenticatable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class MemberResetPasswordVerification extends Notification
{
    use Queueable;

    public function __construct(
        public BaseAuthenticatable $member,
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
            ->greeting(__('messages.reset_password.greeting', ['name' => $this->member->getName()]))
            ->subject(__('messages.reset_password.subject'))
            ->line(new HtmlString(__('messages.reset_password.line_1', ['password' => $this->password])))
            ->line(__('messages.reset_password.line_2'));
    }
}
