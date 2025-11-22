<?php

namespace App\Notifications;

use App\Models\PushNotifications\PushNotificationTask;
use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\WebpushConfig;
use NotificationChannels\Fcm\Resources\WebpushFcmOptions;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class PushNotification extends Notification
{
    use Queueable;

    private PushNotificationTask $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(PushNotificationTask $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return [FcmChannel::class];
    }

    public function toFcm($notifiable): FcmMessage
    {
        $orderId = $this->task['order_id'] === null ? null : (string)$this->task['order_id'];
        // for crm clients (web)
        if (preg_match('/^dispatcher\_.+$/', $this->task['type'])) {
            $company = $this->task->user->getCompany();
            $icon = null;

            if ($company) {
                $media = $company->getFirstMedia(Company::LOGO_FIELD_CARRIER);
                if ($media) {
                    $icon = $media->getFullUrl();
                }
            }

            return FcmMessage::create()
                ->setWebpush(
                    WebpushConfig::create()
                        ->setFcmOptions(
                            $orderId ? WebpushFcmOptions::create()
                                ->setLink(config('frontend.url') . '/orders/' . $this->task['order_id']) : null
                        )
                        ->setData([
                            'order_id' => $orderId,
                            'title' => 'Haulk CRM',
                            'body' => $this->task['message'],
                            'icon' => $icon,
                        ])
                );
        }

        // for mobile clients
        return FcmMessage::create()
            ->setData([
                'order_id' => $orderId,
                'action' => $this->task['type']
            ])
            ->setNotification(
                FcmNotification::create()
                    ->setTitle('Haulk CRM')
                    ->setBody($this->task['message'])
            );
    }
}
