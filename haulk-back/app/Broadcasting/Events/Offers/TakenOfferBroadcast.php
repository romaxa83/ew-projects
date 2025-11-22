<?php

namespace App\Broadcasting\Events\Offers;

class TakenOfferBroadcast extends OfferBroadcast
{

    public const NAME = 'order.offer.taken';

    protected function getName(): string
    {
        return self::NAME;
    }
}
