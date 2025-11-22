<?php
namespace Database\Factories\Notifications;

use App\Enums\Notifications\NotificationAction;
use App\Enums\Notifications\NotificationPlace;
use App\Enums\Notifications\NotificationStatus;
use App\Enums\Notifications\NotificationType;
use App\Models\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'status' => NotificationStatus::NEW(),
            'type' => NotificationType::GPS(),
            'place' => NotificationPlace::BACKOFFICE(),
            'action' => NotificationAction::NONE(),
            'message_key' => 'test',
            'message_attr' => [],
            'meta' => [],
        ];
    }
}
