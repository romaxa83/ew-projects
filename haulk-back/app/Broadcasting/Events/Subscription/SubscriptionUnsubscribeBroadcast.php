<?php


namespace App\Broadcasting\Events\Subscription;

class SubscriptionUnsubscribeBroadcast extends SubscriptionBroadcast
{
    public const NAME = 'subscription.unsubscribe';

    protected function getName(): string
    {
        return self::NAME;
    }
}
