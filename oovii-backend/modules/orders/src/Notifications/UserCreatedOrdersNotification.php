<?php

namespace WezomCms\Orders\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use NotificationChannels\TurboSms\TurboSmsChannel;
use NotificationChannels\TurboSms\TurboSmsMessage;
use WezomCms\Orders\Models\Order;
use WezomCms\Users\Models\User;

class UserCreatedOrdersNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Collection $orders;

    /**
     * Create a new notification instance.
     *
     * @param  Collection  $orders
     */
    public function __construct(Collection $orders)
    {
        $this->orders = $orders;
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
            ->markdown('cms-orders::site.notifications.created-orders', [
                'order' => $this->orders,
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
            'cms-orders::site.checkout.Thank you for your orders №:numbers! We will contact you soon',
            ['numbers' => $this->orders->implode('id', ', ')]
        );

        return TurboSmsMessage::create($content);
    }
}
