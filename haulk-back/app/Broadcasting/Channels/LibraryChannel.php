<?php


namespace App\Broadcasting\Channels;


use App\Broadcasting\Events\Library\CreateLibraryBroadcast;
use App\Broadcasting\Events\Library\DeleteLibraryBroadcast;
use App\Models\Library\LibraryDocument;
use App\Models\Users\User;

class LibraryChannel implements Channel
{
    public const NAME = 'libraries.';

    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getEvents(): array
    {
        return [
            CreateLibraryBroadcast::NAME,
            DeleteLibraryBroadcast::NAME
        ];
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('viewList', LibraryDocument::class);
    }

    public function join(User $user, int $companyId): bool
    {
        return $this->isAllowedForUser($user) && $user->getCompanyId() === $companyId;
    }
}
