<?php

namespace App\Notifications\Orders\Dealer;

use App\Models\Orders\Dealer\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class SendOrderToManagerNotification extends Notification implements ShouldQueue
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
            ->subject(__('messages.dealer.order.manager.subject',[
                'po' => $this->order->po,
            ]))
            ->line(__('messages.dealer.order.manager.body',[
                'po' => $this->order->po,
                'name' => $this->order->dealer?->company?->manager?->name
            ]))
            ->line(new HtmlString('<br>'))
            ;
    }
}
