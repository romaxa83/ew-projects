<?php

namespace Wezom\Users\Notifications\Site;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class UserForgotPasswordNotification extends Notification implements ShouldQueue
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

        return app(MailMessage::class)
            ->template('core::vendor.notifications.email')
            ->greeting(__('users::messages.site.forgot_password.greeting', ['name' => $notifiable->getName()]))
            ->subject(__('users::messages.site.forgot_password.subject'))
            ->line(new HtmlString(__('users::messages.site.forgot_password.line_1')))
            ->line(__('users::messages.site.forgot_password.line_2'))
            ->action(__('users::messages.site.forgot_password.action'), $this->link)
            ->line(__('users::messages.site.forgot_password.line_3'));
    }
}
