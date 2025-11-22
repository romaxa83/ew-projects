<?php

namespace App\Broadcasting\Channels;

use App\Broadcasting\Events\Offers\NewOfferBroadcast;
use App\Broadcasting\Events\Offers\ReleaseOfferBroadcast;
use App\Broadcasting\Events\Offers\TakenOfferBroadcast;
use App\Models\Orders\Order;
use App\Models\Users\User;

class OfferChannel implements Channel
{
    public const NAME = 'offers.';

    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getEvents(): array
    {
        return [
            NewOfferBroadcast::NAME,
            ReleaseOfferBroadcast::NAME,
            TakenOfferBroadcast::NAME,
        ];
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('viewList', Order::class);
    }

    public function join(User $user, int $companyId): bool
    {
        return $user->getCompanyId() === $companyId && $this->isAllowedForUser($user);
    }
}
