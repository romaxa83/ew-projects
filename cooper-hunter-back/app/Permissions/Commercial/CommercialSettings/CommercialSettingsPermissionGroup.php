<?php

namespace App\Permissions\Commercial\CommercialSettings;

use Core\Permissions\BasePermissionGroup;

class CommercialSettingsPermissionGroup extends BasePermissionGroup
{
    public const KEY = 'commercial_settings';

    public function getName(): string
    {
        return __('permissions.commercial_settings.group');
    }
}