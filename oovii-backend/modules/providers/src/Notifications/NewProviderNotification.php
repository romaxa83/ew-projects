<?php

namespace WezomCms\Providers\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use WezomCms\Providers\Models\Provider;

class NewProviderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Provider $provider)
    {}

    public function via($notifiable)
    {
        return ['database'];
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

    public function toArray($notifiable): array
    {
        return [
            'route_name' => 'admin.providers.edit',
            'route_params' => $this->provider->id,
            'icon' => 'fa-envelope-o',
            'color' => 'info',
            'heading' => __('cms-providers::admin.notification.register new provider.title'),
            'description' => __('cms-providers::admin.notification.register new provider.description'),
        ];
    }
}

