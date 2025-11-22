<?php


namespace App\Services\Permissions\Groups;


class PermissionGroupSupportRequests extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'support-requests';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'reply',
            'reply-own',
            'close',
            'close-own',
        ];
    }
}
