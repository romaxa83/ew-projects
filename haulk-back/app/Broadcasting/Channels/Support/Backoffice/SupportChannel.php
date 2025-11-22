<?php


namespace App\Broadcasting\Channels\Support\Backoffice;


use App\Broadcasting\Channels\AdminChannel;
use App\Broadcasting\Events\Support\Backoffice\ChangeRequestLabelBroadcast;
use App\Broadcasting\Events\Support\Backoffice\ChangeRequestStatusBroadcast;
use App\Broadcasting\Events\Support\Backoffice\NewIsNotViewRequestBroadcast;
use App\Broadcasting\Events\Support\Backoffice\NewMessageBroadcast;
use App\Broadcasting\Events\Support\Backoffice\NewRequestBroadcast;
use App\Models\Admins\Admin;

class SupportChannel implements AdminChannel
{

    public const NAME = 'support';

    public const PREFIX = 'private-';

    public static function getNameForAdmin(?Admin $admin = null): string
    {
        return self::NAME;
    }

    public function getPrefix(): string
    {
        return self::PREFIX;
    }

    public function getEvents(): array
    {
        return [
            NewRequestBroadcast::NAME,
            ChangeRequestStatusBroadcast::NAME,
            ChangeRequestLabelBroadcast::NAME,
            NewIsNotViewRequestBroadcast::NAME,
            NewMessageBroadcast::NAME,
        ];
    }

    /**
     * @param Admin $user
     * @return bool
     */
    public function isAllowedForAdmin(Admin $admin): bool
    {
        return $admin->can('support-requests');
    }

    /**
     * @param Admin $admin
     * @return bool
     */
    public function join(Admin $admin): bool
    {
        return $this->isAllowedForAdmin($admin);
    }
}
