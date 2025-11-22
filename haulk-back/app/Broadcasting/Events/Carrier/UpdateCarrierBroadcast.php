<?php

namespace App\Broadcasting\Events\Carrier;

class UpdateCarrierBroadcast extends CarrierBroadcast
{
    public const NAME = 'carrier.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
