<?php

namespace App\Notifications\Members;

use App\Models\BaseAuthenticatable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class MemberForgotPasswordVerification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BaseAuthenticatable $member,
        public string $link,
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
            ->greeting(__('messages.forgot_password.greeting', ['name' => $this->member->getName()]))
            ->subject(__('messages.forgot_password.subject'))
            ->line(__('messages.forgot_password.line_1'))
            ->line(__('messages.forgot_password.line_2'))
            ->line(new HtmlString(__('messages.forgot_password.line_3', ['link' => $this->link])));
    }
}
