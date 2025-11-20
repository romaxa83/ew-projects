<?php

namespace Database\Factories\Notification;

use App\Models\Notification\FcmNotification;
use App\Models\Notification\FcmTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class FcmNotificationFactory extends Factory
{
    protected $model = FcmNotification::class;

    public function definition(): array
    {
        return [
            'status' => FcmNotification::STATUS_CREATED,
            'send_data' => [],
            'response_data' => [],
            'action' => FcmTemplate::PLANNED,
        ];
    }
}

