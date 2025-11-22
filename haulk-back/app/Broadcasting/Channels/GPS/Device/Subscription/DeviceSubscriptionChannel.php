<?php

namespace App\Broadcasting\Channels\GPS\Device\Subscription;

use App\Broadcasting\Channels\Channel;
use App\Broadcasting\Events\GPS\Device\Subscription\ChangeRateBroadcast;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;

class DeviceSubscriptionChannel implements Channel
{

    public const NAME = 'device-subscription.';

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
            ChangeRateBroadcast::NAME
        ];
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAllowedForUser(User $user): bool
    {
        return $user->can('support-requests');
    }

    /**
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function join(User $user, Company $company): bool
    {
        return $user->getCompanyId() === $company->id && $this->isAllowedForUser($user);
    }
}


