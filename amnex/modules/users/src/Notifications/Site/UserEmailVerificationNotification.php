<?php

namespace Wezom\Users\Notifications\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class UserEmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $link)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $home = config('front_routes.frontoffice.home');

        return (new MailMessage())
            ->subject(__('users::messages.site.email_verification.subject'))
            ->greeting(__('users::messages.site.email_verification.greeting', ['name' => $notifiable->getName()]))
            ->line(new HtmlString(__('users::messages.site.email_verification.line_1')))
            ->action(__('users::messages.site.email_verification.action'), $this->link);
    }
}
