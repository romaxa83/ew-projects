<?php

namespace App\Services\Notifications\Inner;

use App\Enums\Notifications\NotificationStatus;
use App\Models\Notifications\Notification;
use App\Services\Notifications\Inner\Patterns\NotificationPatternContract;

abstract class BaseNotificationService implements NotificationPatternContract
{
   abstract function data(): array;

    public function create(): Notification
    {
        $model = new Notification();
        $model->status = data_get($this->data(), 'status', NotificationStatus::NEW());
        $model->type = data_get($this->data(), 'type');
        $model->place = data_get($this->data(), 'place');
        $model->message_key = data_get($this->data(), 'message_key');
        $model->message_attr = data_get($this->data(), 'message_attr', []);
        $model->meta = data_get($this->data(), 'meta', []);
        $model->action = data_get($this->data(), 'action');

        $model->save();

        return $model;
    }
}


