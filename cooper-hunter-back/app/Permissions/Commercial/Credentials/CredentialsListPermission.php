<?php

namespace App\Permissions\Commercial\Credentials;

use Core\Permissions\BasePermission;

class CredentialsListPermission extends BasePermission
{
    public const KEY = CredentialsPermissionGroup::KEY . '.list';

    public function getName(): string
    {
        return __('permissions.commercial_credentials.grants.list');
    }

    public function getPosition(): int
    {
        return 0;
    }
}