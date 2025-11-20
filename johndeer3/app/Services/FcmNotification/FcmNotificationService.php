<?php

namespace App\Services\FcmNotification;

use App\Models\Notification\FcmNotification;
use App\Models\User\User;

class FcmNotificationService
{
    public function __construct()
    {}

    public function create(FcmNotyItemPayload $data): FcmNotification
    {
        $model = new FcmNotification();
        $model->entity_type = User::class;
        $model->entity_id = $data->getUserId();
        $model->status = FcmNotification::STATUS_CREATED;
        $model->action = $data->getMessagePayload()->getType();
        $model->send_data = (array)$data->getMessagePayload();
        $model->save();

        return $model;
    }
}

