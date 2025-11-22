<?php

namespace App\Broadcasting\Events\Offers;


class ReleaseOfferBroadcast extends OfferBroadcast
{

    public const NAME = 'order.offer.release';

    protected function getName(): string
    {
        return self::NAME;
    }
}
