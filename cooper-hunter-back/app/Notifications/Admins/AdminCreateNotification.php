<?php

namespace App\Notifications\Admins;

use App\Models\Admins\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminCreateNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Admin $admin,
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
        return (new MailMessage())
            ->greeting(__('messages.create_admin.greeting', ['name' => $this->admin->getName()]))
            ->subject(__('messages.create_admin.subject'))
            ->line(__('messages.create_admin.line_1'))
            ->line(__('messages.create_admin.line_2'))
            ->line(__('messages.create_admin.line_3'))
            ->markdown(
                'notifications::email',
                [
                    'additional_info' => [
                        __('fields.email') => (string)$this->admin->email,
                        __('fields.password') => $this->password,
                    ],
                ]
            );
    }
}
