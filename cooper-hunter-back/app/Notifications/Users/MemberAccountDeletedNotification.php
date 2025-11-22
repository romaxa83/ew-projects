<?php

namespace App\Notifications\Users;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberAccountDeletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $userName)
    {
    }

    public function via($notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('Account deleted'))
            ->greeting(__('Hello, :Name!', ['name' => $this->userName]))
            ->line(__('Your account has been deleted'));
    }
}
