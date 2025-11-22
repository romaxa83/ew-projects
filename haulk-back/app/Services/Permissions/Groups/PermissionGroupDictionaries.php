<?php


namespace App\Services\Permissions\Groups;


class PermissionGroupDictionaries extends PermissionGroupAbstract
{
    public function getName(): string
    {
        return 'dictionaries';
    }

    public function getPermissions(): array
    {
        return [
            'create',
            'read',
            'update',
            'delete',
        ];
    }
}
