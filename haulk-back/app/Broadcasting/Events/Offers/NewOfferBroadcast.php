<?php

namespace App\Broadcasting\Events\Offers;

class NewOfferBroadcast extends OfferBroadcast
{

    public const NAME = 'order.offer.new';

    protected function getName(): string
    {
        return self::NAME;
    }
}
