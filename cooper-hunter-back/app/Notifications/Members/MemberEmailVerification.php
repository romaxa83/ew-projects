<?php

namespace App\Notifications\Members;

use App\Models\BaseAuthenticatable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemberEmailVerification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BaseAuthenticatable $member, public string $token)
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
            ->greeting(__('messages.email_confirmation.greeting', ['name' => $this->member->getName()]))
            ->subject(__('messages.email_confirmation.subject'))
            ->line(__('messages.email_confirmation.line_1'))
            ->line(__('messages.email_confirmation.line_2'))
            ->action(
                __('messages.email_confirmation.button'),
                config('front_routes.email-confirmation').'/'.$this->token
            );
    }
}
