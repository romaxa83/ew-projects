<?php


namespace App\Broadcasting\Channels\Alerts;


use App\Broadcasting\Channels\Channel;
use App\Broadcasting\Events\Alerts\UserAlertBroadcast;
use App\Models\Users\User;

class UserAlertsChannel implements Channel
{
    public const NAME = 'alerts.';
    public const NAME_POSTFIX = '.user.';
    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId() . self::NAME_POSTFIX . $user->id;
    }

    public function getEvents(): array
    {
        return [
            UserAlertBroadcast::NAME,
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

    public function join(User $user, int $companyId, int $userId): bool
    {
        return $user->id === $userId
            && $user->getCompanyId() === $companyId
            && $this->isAllowedForUser($user);
    }
}
