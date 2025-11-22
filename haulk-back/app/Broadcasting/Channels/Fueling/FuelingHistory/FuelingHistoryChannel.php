<?php

namespace App\Broadcasting\Channels\Fueling\FuelingHistory;

use App\Broadcasting\Channels\Channel;
use App\Broadcasting\Events\Fueling\FuelingHistory\FuelingHistoryBroadcast;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;

class FuelingHistoryChannel implements Channel
{
    public const NAME = 'fueling-history.';

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
            FuelingHistoryBroadcast::NAME
        ];
    }

    /**
     * @param User $user
     * @return bool
     */
    public function isAllowedForUser(User $user): bool
    {
        return $user->can('fueling');
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



