<?php

namespace WezomCms\Orders\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\TurboSms\TurboSmsChannel;
use NotificationChannels\TurboSms\TurboSmsMessage;
use WezomCms\Orders\Models\Order;
use WezomCms\Users\Models\User;

class UserCreatedOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var Order
     */
    private $order;

    /**
     * Create a new notification instance.
     *
     * @param  Order  $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return $notifiable->email ? ['mail'] : [TurboSmsChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(__('cms-orders::site.email.Thank you for your order'))
            ->markdown('cms-orders::site.notifications.created-order', [
                'order' => $this->order,
                'payment' => $this->order->payment ? $this->order->payment->name : '',
                'urlToCabinet' => $notifiable instanceof User ? route('cabinet') : null,
            ]);
    }

    /**
     * @param  mixed  $notifiable
     * @return TurboSmsMessage
     */
    public function toTurboSms($notifiable)
    {
        $content = __(
            'cms-orders::site.checkout.Thank you for your order №:number! We will contact you soon',
            ['number' => $this->order->id]
        );

        return TurboSmsMessage::create($content);
    }
}
