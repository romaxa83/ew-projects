<?php

declare(strict_types=1);

namespace Wezom\Admins\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Wezom\Admins\Models\Admin;
use Wezom\Core\Enums\NotificationTypeEnum;

class AdminEmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Admin $admin, protected string $link)
    {
    }

    public function via(): array
    {
        return [
            NotificationTypeEnum::MAIL->value,
        ];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject(__('admins::messages.admin.email_verification.subject'))
            ->greeting(__('admins::messages.admin.email_verification.greeting', ['name' => $this->admin->getName()]))
            ->line(__('admins::messages.admin.email_verification.line'))
            ->action(__('admins::messages.admin.email_verification.action'), $this->link);
    }
}
