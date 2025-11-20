<?php

namespace WezomCms\Supports\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\Supports\Models\Support;

class SendSupportMessageNotifications extends Notification implements ShouldQueue
{
    use Queueable;

    private Support $message;

    /**
     * Create a new notification instance.
     *
     * @param  Support  $message
     */
    public function __construct(Support $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [
//            'mail',
            'database'
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
//        return (new MailMessage())
//            ->subject(__('cms-services-orders::admin.New message from the service order form'))
//            ->markdown('cms-services-orders::admin.notifications.email', [
//                'serviceOrder' => $this->serviceOrder,
//                'urlToAdmin' => route('admin.services-orders.edit', $this->serviceOrder->id),
//            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'route_name' => 'admin.supports.edit',
            'route_params' => $this->message->id,
            'icon' => 'fa-envelope-o',
            'color' => 'info',
            'heading' => __('cms-supports::admin.Support'),
            'description' => __('cms-supports::admin.New message'),
        ];
    }
}
