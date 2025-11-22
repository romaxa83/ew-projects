<?php

namespace App\Services\Notifications\Inner\Patterns;

use App\Models\Notifications\Notification;

interface NotificationPatternContract
{
    public function create(): Notification;
}


