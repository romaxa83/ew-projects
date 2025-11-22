<?php


namespace App\Broadcasting\Channels\Support\Crm;


use App\Broadcasting\Channels\Channel;
use App\Broadcasting\Events\Support\Crm\ChangeRequestStatusBroadcast;
use App\Broadcasting\Events\Support\Crm\NewMessageBroadcast;
use App\Broadcasting\Events\Support\Crm\NewRequestBroadcast;
use App\Models\Saas\Company\Company;
use App\Models\Users\User;

class SupportChannel implements Channel
{

    public const NAME = 'support.';

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
            NewRequestBroadcast::NAME,
            NewMessageBroadcast::NAME,
            ChangeRequestStatusBroadcast::NAME
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
