<?php


namespace App\Broadcasting\Events\Subscription;

class SubscriptionUpdateBroadcast extends SubscriptionBroadcast
{
    public const NAME = 'subscription.update';

    protected function getName(): string
    {
        return self::NAME;
    }
}
