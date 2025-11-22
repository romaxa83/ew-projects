<?php

namespace Tests\Builders\Notifications;

use App\Enums\Notifications\NotificationStatus;
use App\Models\Notifications\Notification;
use Tests\Builders\BaseBuilder;

class NotificationBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Notification::class;
    }

    public function status(NotificationStatus $value): self
    {
        $this->data['status'] = $value;
        return $this;
    }
}
