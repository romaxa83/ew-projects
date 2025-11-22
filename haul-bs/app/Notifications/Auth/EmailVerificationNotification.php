<?php

namespace App\Notifications\Auth;

use App\Foundations\Models\BaseAuthenticatableModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class EmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public BaseAuthenticatableModel $user,
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
            ->greeting(__('mail.email_verification.greeting', ['name' => $this->user->getName()]))
            ->subject(__('mail.email_verification.subject'))
            ->line(new HtmlString(__('mail.email_verification.line_1', ['link' => $this->link])));
    }
}


