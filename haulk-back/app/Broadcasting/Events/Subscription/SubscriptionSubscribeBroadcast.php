<?php


namespace App\Broadcasting\Events\Subscription;

class SubscriptionSubscribeBroadcast extends SubscriptionBroadcast
{
    public const NAME = 'subscription.subscribe';

    protected function getName(): string
    {
        return self::NAME;
    }
}
