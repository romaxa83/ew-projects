<?php

namespace App\Notifications\Warranty;

use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class WarrantyStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public WarrantyRegistration $warranty)
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
            ->subject(__('messages.warranty.notification.subject'))
            ->markdown(
                'notifications::email',
                [
                    'warranty' => $this->warranty,
                ]
            );
    }
}
