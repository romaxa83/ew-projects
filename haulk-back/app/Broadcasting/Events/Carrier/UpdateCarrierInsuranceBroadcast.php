<?php

namespace App\Broadcasting\Events\Carrier;

class UpdateCarrierInsuranceBroadcast extends CarrierBroadcast
{

    public const NAME = 'carrier-insurance.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
