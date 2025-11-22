<?php


namespace App\Broadcasting\Channels\Support\Crm;


use App\Broadcasting\Channels\Channel;
use App\Broadcasting\Events\Support\Crm\NewIsNotReadMessageBroadcast;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;

class SupportUserChannel implements Channel
{

    public const NAME = 'support';

    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . '.' . $user->getCompanyId() . '.user.' . $user->id;
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function getEvents(): array
    {
        return [
            NewIsNotReadMessageBroadcast::NAME
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

    public function join(User $authUser, Company $company, User $user): bool
    {

        return $authUser->getCompanyId() === $company->id && $user->id === $authUser->id && $this->isAllowedForUser($authUser);
    }
}
