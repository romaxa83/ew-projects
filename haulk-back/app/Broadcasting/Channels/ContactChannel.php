<?php


namespace App\Broadcasting\Channels;


use App\Broadcasting\Events\Contact\CreateContactBroadcast;
use App\Broadcasting\Events\Contact\DeleteContactBroadcast;
use App\Broadcasting\Events\Contact\UpdateContactBroadcast;
use App\Models\Contacts\Contact;
use App\Models\Users\User;

class ContactChannel implements Channel
{
    public const NAME = 'contacts.';

    public const PREFIX = 'private-';

    public static function getNameForUser(User $user): string
    {
        return self::NAME . $user->getCompanyId();
    }

    public function getEvents(): array
    {
        return [
            CreateContactBroadcast::NAME,
            UpdateContactBroadcast::NAME,
            DeleteContactBroadcast::NAME
        ];
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function isAllowedForUser(User $user): bool
    {
        return $user->can('viewList', Contact::class);
    }

    public function join(User $user, int $companyId): bool
    {
        return $this->isAllowedForUser($user) && $user->getCompanyId() === $companyId;
    }
}
