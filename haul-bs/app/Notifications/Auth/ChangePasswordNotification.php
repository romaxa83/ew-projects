<?php

namespace App\Notifications\Auth;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangePasswordNotification extends Notification
{
    use Queueable;

    public function __construct(protected User $user)
    {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject("Password change")
            ->greeting("Hello, " . $this->user->full_name)
            ->line("Your password was changed.");

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
