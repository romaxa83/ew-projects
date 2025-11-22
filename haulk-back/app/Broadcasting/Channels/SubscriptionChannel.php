<?php


namespace App\Broadcasting\Channels;

use App\Broadcasting\Events\Subscription\SubscriptionSubscribeBroadcast;
use App\Broadcasting\Events\Subscription\SubscriptionUnsubscribeBroadcast;
use App\Broadcasting\Events\Subscription\SubscriptionUpdateBroadcast;
use App\Models\Users\User;

class SubscriptionChannel implements Channel
{
    public const NAME = 'subscription.';
    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function getEvents(): array
    {
        return [
            SubscriptionUpdateBroadcast::NAME,
            SubscriptionSubscribeBroadcast::NAME,
            SubscriptionUnsubscribeBroadcast::NAME
        ];
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('profile');
    }

    public function join(User $user, int $companyId): bool
    {
        return $user->getCompanyId() === $companyId && $this->isAllowedForUser($user);
    }
}
