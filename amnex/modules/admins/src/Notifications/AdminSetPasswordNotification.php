<?php

declare(strict_types=1);

namespace Wezom\Admins\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Enums\NotificationTypeEnum;

class AdminSetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Admin $admin,
        protected string $link
    ) {
    }

    public function via(mixed $notifiable): array
    {
        return [
            NotificationTypeEnum::MAIL->value,
        ];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return app(MailMessage::class)
            ->greeting(__('admins::messages.admin.set_password.greeting', ['name' => $this->admin->getName()]))
            ->subject(__('admins::messages.admin.set_password.subject'))
            ->line(__('admins::messages.admin.set_password.line_1'))
            ->line(__('admins::messages.admin.set_password.line_2'))
            ->action(__('admins::messages.admin.set_password.action'), $this->link);
    }
}
