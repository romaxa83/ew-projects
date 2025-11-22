<?php


namespace App\Broadcasting\Channels\Alerts;


use App\Broadcasting\Channels\Channel;
use App\Broadcasting\Events\Alerts\CompanyAlertBroadcast;
use App\Models\Users\User;

class CompanyAlertsChannel implements Channel
{
    public const NAME = 'alerts.';
    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getEvents(): array
    {
        return [
            CompanyAlertBroadcast::NAME,
        ];
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('alerts');
    }

    public function join(User $user, int $companyId): bool
    {
        return $user->getCompanyId() === $companyId
            && $this->isAllowedForUser($user);
    }
}
