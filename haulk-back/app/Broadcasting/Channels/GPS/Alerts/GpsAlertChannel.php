<?php

namespace App\Broadcasting\Channels\GPS\Alerts;

use App\Broadcasting\Channels\Channel;
use App\Broadcasting\Events\GPS\Alerts\GpsAlertsCreateBroadcast;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;

class GpsAlertChannel implements Channel
{
    public const NAME = 'gps-alerts.';

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
            GpsAlertsCreateBroadcast::NAME
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



