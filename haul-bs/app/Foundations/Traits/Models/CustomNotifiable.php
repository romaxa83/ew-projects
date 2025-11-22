<?php

namespace App\Foundations\Traits\Models;

use App\Foundations\ValueObjects\Email;
use Illuminate\Notifications\HasDatabaseNotifications;
use Illuminate\Notifications\RoutesNotifications;

trait CustomNotifiable
{
    use HasDatabaseNotifications, RoutesNotifications;

    public function routeNotificationForMail($notification)
    {
        return  $this->email instanceof Email
            ? $this->email->getValue()
            : $this->email
            ;
    }
}

