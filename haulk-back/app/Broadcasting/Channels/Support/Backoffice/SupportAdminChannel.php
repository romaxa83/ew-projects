<?php


namespace App\Broadcasting\Channels\Support\Backoffice;


use App\Broadcasting\Channels\AdminChannel;
use App\Broadcasting\Events\Support\Backoffice\NewIsNotReadMessageBroadcast;
use App\Models\Admins\Admin;

class SupportAdminChannel implements AdminChannel
{

    public const NAME = 'support';

    public const PREFIX = 'private-';

    public static function getNameForAdmin(Admin $admin): string
    {
        return self::NAME . '.admin.' . $admin->id;
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
     * @param Admin $admin
     * @return bool
     */
    public function isAllowedForAdmin(Admin $admin): bool
    {
        return $admin->can('support-requests');
    }

    public function join(Admin $authAdmin, Admin $admin): bool
    {

        return $admin->id === $authAdmin->id && $this->isAllowedForAdmin($admin);
    }
}
