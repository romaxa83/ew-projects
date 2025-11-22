<?php

namespace App\Notifications\Orders\Dealer;

use App\Models\Orders\Dealer\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class SendOrderToCommercialManagerNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(protected Order $order)
    {}

    public function via(mixed $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('messages.dealer.order.commercial_manager.subject', [
                'po' => $this->order->po,
            ]))
            ->line(__('messages.dealer.order.commercial_manager.body',[
                'po' => $this->order->po,
                'name' => $this->order->dealer?->company?->commercialManager?->name
            ]))
            ->line(new HtmlString('<br>'))
            ;
    }
}
