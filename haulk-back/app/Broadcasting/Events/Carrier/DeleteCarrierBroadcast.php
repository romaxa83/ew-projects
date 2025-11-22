<?php

namespace App\Broadcasting\Events\Carrier;

class DeleteCarrierBroadcast extends CarrierBroadcast
{

    public const NAME = 'carrier.delete';

    protected function getName(): string
    {
        return self::NAME;
    }
}
