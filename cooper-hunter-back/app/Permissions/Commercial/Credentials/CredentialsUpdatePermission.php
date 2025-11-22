<?php

namespace App\Permissions\Commercial\Credentials;

use Core\Permissions\BasePermission;

class CredentialsUpdatePermission extends BasePermission
{
    public const KEY = CredentialsPermissionGroup::KEY . '.update';

    public function getName(): string
    {
        return __('permissions.commercial_credentials.grants.update');
    }

    public function getPosition(): int
    {
        return 2;
    }
}