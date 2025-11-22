<?php

namespace App\Notifications\Orders\Dealer;

use App\Models\Commercial\CommercialSettings;
use App\Models\Orders\Dealer\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class SendApprovedOrderToDealerNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Order $order,
        protected bool $changed,
    )
    {}

    public function via(mixed $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $settings = CommercialSettings::first();

        return (new MailMessage())
            ->subject(__('messages.dealer.order.as_approved.subject'))
            ->view( 'mail.notification.approved-order', [
                'order' => $this->order,
                'settings' => $settings,
                'changed' => $this->changed
            ])
            ;
    }
}
