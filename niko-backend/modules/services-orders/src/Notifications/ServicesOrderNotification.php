<?php

namespace WezomCms\ServicesOrders\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use WezomCms\ServicesOrders\Models\ServicesOrder;

class ServicesOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var ServicesOrder
     */
    private $serviceOrder;

    /**
     * Create a new notification instance.
     *
     * @param  ServicesOrder  $serviceOrder
     */
    public function __construct(ServicesOrder $serviceOrder)
    {
        $this->serviceOrder = $serviceOrder;
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
            'route_name' => 'admin.services-orders.index',
            'route_params' => ['id' => $this->serviceOrder->id],
            'permission' => 'services-orders.edit',
            'icon' => 'fa-envelope-o',
            'color' => 'info',
            'heading' => __('cms-services-orders::admin.Service orders'),
            'description' => __('cms-services-orders::admin.New message from the service order form'),
        ];
    }
}
