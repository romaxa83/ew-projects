<?php


namespace App\Broadcasting\Channels;


use App\Broadcasting\Events\User\ActivateUserBroadcast;
use App\Broadcasting\Events\User\CreateUserBroadcast;
use App\Broadcasting\Events\User\DeactivateUserBroadcast;
use App\Broadcasting\Events\User\DeleteUserBroadcast;
use App\Broadcasting\Events\User\UpdateUserBroadcast;
use App\Models\Users\User;

class UserChannel implements Channel
{
    public const NAME = 'users.';

    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getEvents(): array
    {
        return [
            ActivateUserBroadcast::NAME,
            DeactivateUserBroadcast::NAME,
            CreateUserBroadcast::NAME,
            UpdateUserBroadcast::NAME,
            DeleteUserBroadcast::NAME
        ];
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('users');
    }

    public function join(User $user, int $companyId): bool
    {
        return $this->isAllowedForUser($user) && $user->getCompanyId() === $companyId;
    }
}
