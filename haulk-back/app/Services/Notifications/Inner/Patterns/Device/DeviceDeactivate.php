<?php

namespace App\Services\Notifications\Inner\Patterns\Device;

use App\Enums\Notifications\NotificationAction;
use App\Enums\Notifications\NotificationPlace;
use App\Enums\Notifications\NotificationStatus;
use App\Enums\Notifications\NotificationType;
use App\Enums\Saas\GPS\DeviceStatus;
use App\Models\Notifications\Notification;
use App\Models\Saas\GPS\Device;
use App\Services\Notifications\Inner\Patterns\NotificationPatternContract;

class DeviceDeactivate implements NotificationPatternContract
{
    private Device $device;
    private DeviceStatus $status;

    public function __construct(
        Device $device,
        DeviceStatus $status
    )
    {
        $this->device = $device;
        $this->status = $status;
    }

    public function create(): Notification
    {
        $msg = 'notification.device.request_activate';
        if($this->status->isInactive()) $msg = 'notification.device.request_deactivate';

        $model = new Notification();
        $model->status = NotificationStatus::NEW();
        $model->type = NotificationType::GPS();
        $model->place = NotificationPlace::BACKOFFICE();
        $model->message_key = $msg;
        $model->message_attr = [
            'company_name' => $this->device->company->name,
            'imei' => $this->device->imei
        ];
        $model->meta = [];
        $model->action = NotificationAction::NONE;

        $model->save();

        return $model;
    }
}

