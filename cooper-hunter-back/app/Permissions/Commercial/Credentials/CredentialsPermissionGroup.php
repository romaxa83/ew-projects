<?php

namespace App\Permissions\Commercial\Credentials;

use Core\Permissions\BasePermissionGroup;

class CredentialsPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'commercial_credentials';

    public function getName(): string
    {
        return __('permissions.commercial_credentials.group');
    }
}