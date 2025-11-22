<?php

namespace App\Broadcasting\Events\Carrier;

class UpdateCarrierNotificationBroadcast extends CarrierBroadcast
{

    public const NAME = 'carrier-notification.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
