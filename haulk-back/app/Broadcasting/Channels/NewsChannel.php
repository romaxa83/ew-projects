<?php


namespace App\Broadcasting\Channels;


use App\Broadcasting\Events\News\ActivateNewsBroadcast;
use App\Broadcasting\Events\News\CreateNewsBroadcast;
use App\Broadcasting\Events\News\DeactivateNewsBroadcast;
use App\Broadcasting\Events\News\DeleteNewsBroadcast;
use App\Broadcasting\Events\News\UpdateNewsBroadcast;
use App\Models\Users\User;

class NewsChannel implements Channel
{
    public const NAME = 'news.';

    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getEvents(): array
    {
        return [
            CreateNewsBroadcast::NAME,
            UpdateNewsBroadcast::NAME,
            DeleteNewsBroadcast::NAME,
            ActivateNewsBroadcast::NAME,
            DeactivateNewsBroadcast::NAME
        ];
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('news');
    }

    public function join(User $user, int $companyId): bool
    {
        return $this->isAllowedForUser($user) && $user->getCompanyId() === $companyId;
    }
}
