<?php

namespace App\Notifications\Alerts;

use App\Dto\Alerts\Fcm\FcmData;
use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Fcm\FcmChannel;
use NotificationChannels\Fcm\FcmMessage;
use NotificationChannels\Fcm\Resources\AndroidConfig;
use NotificationChannels\Fcm\Resources\AndroidNotification;
use NotificationChannels\Fcm\Resources\Notification as FcmNotification;

class AlertFcmNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private FcmData $fcmData)
    {}

    public function via(Admin|User|Technician $notifiable): array
    {
        if ($notifiable->routeNotificationForFcm() === null) {
            return [];
        }
        return [
            FcmChannel::class
        ];
    }

    public function toFcm($notifiable): FcmMessage
    {
        return FcmMessage::create()
            ->setData($this->fcmData->data)
            ->setNotification(
                FcmNotification::create()
                    ->setTitle($this->fcmData->title)
                    ->setBody($this->fcmData->body)
            )
            ->setAndroid(
                AndroidConfig::create()
                    ->setCollapseKey($this->fcmData->type)
                    ->setData($this->fcmData->data)
                    ->setNotification(
                        AndroidNotification::create()
                            ->setTitle($this->fcmData->title)
                            ->setBody($this->fcmData->body)
                    )
            );
    }
}
