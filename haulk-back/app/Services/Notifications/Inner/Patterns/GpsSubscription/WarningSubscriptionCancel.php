<?php

namespace App\Services\Notifications\Inner\Patterns\GpsSubscription;

use App\Enums\Notifications\NotificationAction;
use App\Enums\Notifications\NotificationPlace;
use App\Enums\Notifications\NotificationType;
use App\Models\Saas\GPS\DeviceSubscription;
use App\Services\Notifications\Inner\BaseNotificationService;

class WarningSubscriptionCancel extends BaseNotificationService
{
    private DeviceSubscription $model;

    public function __construct(DeviceSubscription $model)
    {
        $this->model = $model;
    }

    public function data(): array
    {
        return [
            'type' => NotificationType::GPS_SUBSCRIPTION(),
            'place' => NotificationPlace::BACKOFFICE(),
            'message_key' => 'notification.gps_subscription.warning_cancel',
            'message_attr' => [
                'company_name' => $this->model->company->name,
            ],
            'meta' => [
                'company_id' => $this->model->company_id,
                'company_name' => $this->model->company->name,
            ],
            'action' => NotificationAction::TO_DEVICE_CANCEL_SUBSCRIPTION
        ];
    }
}


